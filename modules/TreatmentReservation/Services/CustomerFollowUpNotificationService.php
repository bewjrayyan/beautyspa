<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class CustomerFollowUpNotificationService
{
    public function sendDueFollowUps(): int
    {
        if (! setting('whatsapp_customer_followup_enabled', false)) {
            return 0;
        }

        $days = max(1, (int) setting('whatsapp_customer_followup_days', 7));
        $targetDate = today()->subDays($days)->toDateString();
        $sent = 0;

        TreatmentBooking::query()
            ->with(['product', 'beautician'])
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->whereNull('followup_sent_at')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->whereDate('appointment_date', $targetDate)
            ->orderBy('id')
            ->chunkById(50, function ($bookings) use (&$sent) {
                foreach ($this->groupByCustomerPhone($bookings) as $bookingsForPhone) {
                    if ($this->sendFollowUpForPhoneGroup($bookingsForPhone)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }


    /**
     * @param  Collection<int, TreatmentBooking>  $bookings
     * @return Collection<string, Collection<int, TreatmentBooking>>
     */
    private function groupByCustomerPhone(Collection $bookings): Collection
    {
        return $bookings->groupBy(function (TreatmentBooking $booking) {
            $normalized = PhoneNumber::normalize((string) $booking->customer_phone);

            return $normalized !== '' ? $normalized : trim((string) $booking->customer_phone);
        });
    }


    /**
     * @param  Collection<int, TreatmentBooking>  $bookingsForPhone
     */
    private function sendFollowUpForPhoneGroup(Collection $bookingsForPhone): bool
    {
        /** @var TreatmentBooking $primary */
        $primary = $bookingsForPhone->sortBy('id')->first();

        if (! $this->claimFollowUp($primary)) {
            return false;
        }

        try {
            $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                trim((string) $primary->customer_phone),
                $this->buildMessage($primary),
                [
                    'source' => 'treatment.booking.followup',
                    'dedupe_key' => 'booking:' . $primary->id . ':followup',
                ]
            );

            if (! $delivered) {
                $this->releaseFollowUpClaim($primary);

                return false;
            }
        } catch (\Throwable $exception) {
            Log::error('Customer follow-up notification failed', [
                'booking_id' => $primary->id,
                'message' => $exception->getMessage(),
            ]);

            $this->releaseFollowUpClaim($primary);

            return false;
        }

        $this->markFollowUpSentForSiblings($bookingsForPhone, $primary->id);

        return true;
    }


    /**
     * @param  Collection<int, TreatmentBooking>  $bookingsForPhone
     */
    private function markFollowUpSentForSiblings(Collection $bookingsForPhone, int $primaryId): void
    {
        $siblingIds = $bookingsForPhone
            ->pluck('id')
            ->filter(fn (int $id) => $id !== $primaryId)
            ->all();

        if ($siblingIds === []) {
            return;
        }

        TreatmentBooking::query()
            ->whereIn('id', $siblingIds)
            ->whereNull('followup_sent_at')
            ->update(['followup_sent_at' => now()]);
    }


    private function claimFollowUp(TreatmentBooking $booking): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->whereNull('followup_sent_at')
            ->update(['followup_sent_at' => now()]) === 1;
    }


    private function releaseFollowUpClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['followup_sent_at' => null]);
    }


    private function buildMessage(TreatmentBooking $booking): string
    {
        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';

        return WhatsAppMessageTemplate::render('whatsapp_customer_followup_message', [
            'store' => $store,
            'customer' => $customer,
            'treatment' => $treatment,
            'date' => $date,
        ], implode("\n", [
            "💆 *Susulan — {$store}*",
            '',
            "Hai {$customer},",
            '',
            "Sudah {$date} sejak rawatan *{$treatment}* anda. Bagaimana keadaan anda?",
            '',
            'Hubungi kami jika ingin tempah sesi seterusnya. Terima kasih!',
        ]));
    }
}
