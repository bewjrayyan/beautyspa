<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
use Modules\User\Services\OneSenderWhatsAppService;

class BeauticianTbaReminderService
{
    public function sendDueReminders(?Carbon $at = null): int
    {
        if (
            ! Schema::hasColumn('treatment_bookings', 'tba_reminder_sent_at')
            || ! setting('whatsapp_beautician_tba_reminder_enabled', true)
            || ! OneSenderWhatsAppService::isConfigured()
        ) {
            return 0;
        }

        $now = ($at ?? now())->copy();

        if (! $this->canSendAt($now)) {
            return 0;
        }

        $repeatDays = max(1, min(30, (int) setting('whatsapp_beautician_tba_reminder_repeat_days', 1)));
        $eligibleBefore = $now->copy()->startOfDay()->subDays($repeatDays - 1);
        $sent = 0;

        TreatmentBooking::query()
            ->with(['beautician.user', 'product'])
            ->whereNotNull('beautician_id')
            ->tbaSchedule()
            ->where(function ($query) use ($eligibleBefore): void {
                $query->whereNull('tba_reminder_sent_at')
                    ->orWhere('tba_reminder_sent_at', '<', $eligibleBefore);
            })
            ->orderBy('id')
            ->chunkById(50, function ($bookings) use ($now, $eligibleBefore, &$sent): void {
                foreach ($bookings as $booking) {
                    if ($this->deliverReminder($booking, $now, $eligibleBefore)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }


    public function sendManualReminder(TreatmentBooking $booking, bool $resend = false): bool
    {
        if (! setting('whatsapp_beautician_tba_reminder_enabled', true)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.beautician_tba_reminder_disabled'));
        }

        if (! OneSenderWhatsAppService::isConfigured()) {
            throw new \InvalidArgumentException(TrLang::trans('admin.calendar.whatsapp_not_configured'));
        }

        if (! $this->canSendReminder($booking)) {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.beautician_tba_reminder_not_eligible'));
        }

        if ($resend && $booking->tba_reminder_sent_at) {
            TreatmentBooking::query()
                ->whereKey($booking->id)
                ->update(['tba_reminder_sent_at' => null]);

            $booking->refresh();
        }

        $now = now();
        $repeatDays = max(1, min(30, (int) setting('whatsapp_beautician_tba_reminder_repeat_days', 1)));
        $eligibleBefore = $now->copy()->startOfDay()->subDays($repeatDays - 1);

        return $this->deliverReminder($booking, $now, $eligibleBefore, logActivity: true);
    }


    public function canSendReminder(TreatmentBooking $booking): bool
    {
        $booking->loadMissing('beautician.user');

        $recipients = app(BeauticianWhatsAppRecipientResolver::class)->resolve($booking);

        return Schema::hasColumn('treatment_bookings', 'tba_reminder_sent_at')
            && $recipients !== []
            && $booking->beautician_id
            && $booking->isTbaSchedule()
            && in_array($booking->status, [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ], true)
            && OneSenderWhatsAppService::isConfigured()
            && (bool) setting('whatsapp_beautician_tba_reminder_enabled', true);
    }


    /**
     * @return array<string, mixed>
     */
    public function reminderMeta(TreatmentBooking $booking): array
    {
        $sentAt = $booking->tba_reminder_sent_at;
        $canSend = $this->canSendReminder($booking);

        return [
            'beautician_reminder_sent_at' => $sentAt?->toIso8601String(),
            'beautician_reminder_sent_label' => $sentAt
                ? $sentAt->format('d M Y, H:i')
                : null,
            'beautician_reminder_sent' => $sentAt !== null,
            'can_send_beautician_reminder' => $canSend,
            'can_resend_beautician_reminder' => $canSend && $sentAt !== null,
        ];
    }


    public function canSendAt(Carbon $at): bool
    {
        $sendTime = trim((string) setting('whatsapp_beautician_tba_reminder_time', '09:00'));

        if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $sendTime)) {
            $sendTime = '09:00';
        }

        [$hour, $minute] = array_map('intval', explode(':', $sendTime));
        $sendAt = $at->copy()->setTime($hour, $minute);

        return $at->greaterThanOrEqualTo($sendAt);
    }


    public function message(TreatmentBooking $booking): string
    {
        $booking->loadMissing(['beautician', 'product']);

        $store = (string) setting('store_name', config('app.name'));
        $beautician = $booking->beautician?->name ?: 'Beautician';
        $customer = $booking->customer_full_name ?: '—';
        $treatment = $booking->product?->name ?: '—';
        $orderId = $booking->order_id ? (string) $booking->order_id : '—';
        $reference = $booking->referenceCode();
        $portalUrl = route('admin.treatment_reservations.portal');

        return WhatsAppMessageTemplate::render('whatsapp_beautician_tba_reminder_message', [
            'store' => $store,
            'beautician' => $beautician,
            'customer' => $customer,
            'treatment' => $treatment,
            'order_id' => $orderId,
            'reference' => $reference,
            'portal_url' => $portalUrl,
        ], implode("\n", [
            "📅 *Temujanji TBA Belum Dijadualkan — {$store}*",
            '',
            "Hai {$beautician},",
            '',
            "Order #{$orderId} untuk {$customer} masih belum mempunyai tarikh dan masa temujanji.",
            "Rawatan: {$treatment}",
            "Rujukan: {$reference}",
            '',
            'Sila masuk ke portal anda dan jadualkan slot temujanji secepat mungkin.',
            "Portal: {$portalUrl}",
        ]));
    }


    private function deliverReminder(
        TreatmentBooking $booking,
        Carbon $now,
        Carbon $eligibleBefore,
        bool $logActivity = false,
    ): bool {
        $recipients = app(BeauticianWhatsAppRecipientResolver::class)->resolve($booking);

        if ($recipients === [] || ! $this->claimReminder($booking, $now, $eligibleBefore)) {
            return false;
        }

        try {
            $accepted = false;

            foreach ($recipients as $phone) {
                $accepted = app(OneSenderWhatsAppService::class)->sendNotification(
                    $phone,
                    $this->message($booking),
                    [
                        'source' => 'treatment.beautician.tba-reminder',
                        'dedupe_key' => 'booking:' . $booking->id . ':beautician_tba_reminder:' . ($logActivity
                            ? 'manual:' . $now->format('YmdHis')
                            : $now->format('Ymd')) . ':recipient:' . substr(hash('sha256', $phone), 0, 16),
                        'immediate' => $logActivity,
                    ]
                ) || $accepted;
            }

            if (! $accepted) {
                $this->releaseReminderClaim($booking);

                return false;
            }

            if ($logActivity) {
                app(TreatmentBookingActivityLogger::class)->logBeauticianReminderSent($booking);
            }

            return $accepted;
        } catch (\Throwable $exception) {
            $this->releaseReminderClaim($booking);
            Log::error('Beautician TBA reminder failed', [
                'booking_id' => $booking->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }


    private function claimReminder(TreatmentBooking $booking, Carbon $now, Carbon $eligibleBefore): bool
    {
        return TreatmentBooking::query()
            ->whereKey($booking->id)
            ->whereNotNull('beautician_id')
            ->tbaSchedule()
            ->where(function ($query) use ($eligibleBefore): void {
                $query->whereNull('tba_reminder_sent_at')
                    ->orWhere('tba_reminder_sent_at', '<', $eligibleBefore);
            })
            ->update(['tba_reminder_sent_at' => $now]) === 1;
    }


    private function releaseReminderClaim(TreatmentBooking $booking): void
    {
        TreatmentBooking::query()
            ->whereKey($booking->id)
            ->update(['tba_reminder_sent_at' => null]);
    }
}
