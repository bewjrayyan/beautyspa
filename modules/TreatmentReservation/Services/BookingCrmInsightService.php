<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class BookingCrmInsightService
{
    /** @var array<string, array{visit_count: int, last_treatment: ?string, last_visit_date: ?string}> */
    private array $customerStatsCache = [];

    /** @var array<string, ?string> */
    private array $loyaltyTierCache = [];

    public function __construct(
        private UpcomingJobUrgencyService $urgency,
    ) {
    }


    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function enrichPayload(TreatmentBooking $booking, array $payload): array
    {
        $phone = PhoneNumber::normalize((string) ($booking->customer_phone ?? ''));

        if ($phone !== '') {
            $stats = $this->customerStatsForPhone($phone);
            $payload['customer_visit_count'] = $stats['visit_count'];
            $payload['customer_last_treatment'] = $stats['last_treatment'];
            $payload['customer_last_visit_date'] = $stats['last_visit_date'];
            $payload['customer_history_label'] = $this->customerHistoryLabel($stats);
            $payload['loyalty_tier_name'] = $this->loyaltyTierForPhone($phone);
        } else {
            $payload['customer_visit_count'] = 0;
            $payload['customer_last_treatment'] = null;
            $payload['customer_last_visit_date'] = null;
            $payload['customer_history_label'] = null;
            $payload['loyalty_tier_name'] = null;
        }

        $payload['inline_alerts'] = $this->urgency->inlineAlertsFor($booking);
        $payload['can_reschedule_manual'] = $booking->isManualEditable();

        return array_merge(
            $payload,
            app(CustomerAppointmentReminderService::class)->reminderMeta($booking)
        );
    }


    /**
     * @param  Collection<int, TreatmentBooking>  $bookings
     * @param  Collection<int, array<string, mixed>>|array<int, array<string, mixed>>  $payloads
     * @return array<int, array<string, mixed>>
     */
    public function enrichMany(Collection $bookings, Collection|array $payloads): array
    {
        $payloadsById = collect($payloads)->keyBy('id');

        return $bookings
            ->map(fn (TreatmentBooking $booking) => $this->enrichPayload(
                $booking,
                $payloadsById->get($booking->id, $booking->toCalendarPayload()),
            ))
            ->values()
            ->all();
    }


    /**
     * @return array{visit_count: int, last_treatment: ?string, last_visit_date: ?string}
     */
    private function customerStatsForPhone(string $normalizedPhone): array
    {
        if (isset($this->customerStatsCache[$normalizedPhone])) {
            return $this->customerStatsCache[$normalizedPhone];
        }

        $base = TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->with('product');

        $visitCount = (clone $base)->count();

        $last = (clone $base)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->first();

        return $this->customerStatsCache[$normalizedPhone] = [
            'visit_count' => $visitCount,
            'last_treatment' => $last?->product?->name,
            'last_visit_date' => $last?->appointment_date?->format('d M Y'),
        ];
    }


    private function loyaltyTierForPhone(string $normalizedPhone): ?string
    {
        if (isset($this->loyaltyTierCache[$normalizedPhone])) {
            return $this->loyaltyTierCache[$normalizedPhone];
        }

        if (! is_module_enabled('Loyalty')) {
            return $this->loyaltyTierCache[$normalizedPhone] = null;
        }

        $variants = PhoneNumber::variants($normalizedPhone);

        if ($variants === []) {
            return $this->loyaltyTierCache[$normalizedPhone] = null;
        }

        $userId = User::query()
            ->whereIn('phone', $variants)
            ->value('id');

        if (! $userId) {
            return $this->loyaltyTierCache[$normalizedPhone] = null;
        }

        $wallet = LoyaltyWallet::query()
            ->with('tier')
            ->where('user_id', $userId)
            ->first();

        return $this->loyaltyTierCache[$normalizedPhone] = $wallet?->tier?->translatedName();
    }


    /**
     * @param  array{visit_count: int, last_treatment: ?string, last_visit_date: ?string}  $stats
     */
    private function customerHistoryLabel(array $stats): ?string
    {
        if ($stats['visit_count'] <= 0 && blank($stats['last_treatment'])) {
            return TrLang::trans('admin.crm.customer_new');
        }

        if ($stats['visit_count'] > 0 && filled($stats['last_treatment'])) {
            return TrLang::trans('admin.crm.customer_history', [
                'count' => $stats['visit_count'],
                'treatment' => $stats['last_treatment'],
            ]);
        }

        if ($stats['visit_count'] > 0) {
            return TrLang::trans('admin.crm.customer_visits', ['count' => $stats['visit_count']]);
        }

        return null;
    }

}
