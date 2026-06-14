<?php

namespace Modules\TreatmentReservation\Services;

use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class CustomerCrmProfileService
{
    public function __construct(
        private CustomerAppointmentReminderService $reminders,
    ) {
    }


    /**
     * @return array<string, mixed>
     */
    public function forBooking(TreatmentBooking $booking): array
    {
        $booking->loadMissing(['product', 'beautician', 'category']);

        $phone = PhoneNumber::normalize((string) ($booking->customer_phone ?? ''));

        if ($phone === '') {
            return $this->profileFromBookingOnly($booking);
        }

        return $this->buildProfile($phone, $booking);
    }


    /**
     * @return array<string, mixed>
     */
    public function forPhone(string $phone): array
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            throw new \InvalidArgumentException(TrLang::trans('admin.crm.profile_phone_required'));
        }

        $latestBooking = TreatmentBooking::query()
            ->matchingCustomerPhone($normalized)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->first();

        return $this->buildProfile($normalized, $latestBooking);
    }


    /**
     * @return array<string, mixed>
     */
    private function buildProfile(string $normalizedPhone, ?TreatmentBooking $contextBooking): array
    {
        $user = $this->userForPhone($normalizedPhone);
        $stats = $this->visitStats($normalizedPhone);
        $loyaltyTier = $this->loyaltyTierForUser($user);

        $displayBooking = $contextBooking ?? TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->first();

        $customerName = $displayBooking?->customer_full_name
            ?: ($user?->full_name ?: TrLang::trans('admin.crm.ledger_unknown_client'));

        $customerEmail = $displayBooking?->customer_email ?: $user?->email;

        return [
            'customer_name' => $customerName,
            'customer_phone' => $displayBooking?->customer_phone ?: $normalizedPhone,
            'customer_email' => $customerEmail,
            'visit_count' => $stats['visit_count'],
            'last_treatment' => $stats['last_treatment'],
            'last_visit_date' => $stats['last_visit_date'],
            'customer_history_label' => $this->historyLabel($stats),
            'loyalty_tier_name' => $loyaltyTier,
            'user_id' => $user?->id,
            'user_admin_url' => $user
                ? route('admin.users.edit', $user)
                : null,
            'current_booking_id' => $contextBooking?->id,
            'visit_history' => $this->visitHistory($normalizedPhone),
            'upcoming_bookings' => $this->upcomingBookings($normalizedPhone),
            'reminder_bookings' => $this->reminderBookings($normalizedPhone),
        ];
    }


    /**
     * @return array<string, mixed>
     */
    private function profileFromBookingOnly(TreatmentBooking $booking): array
    {
        $payload = $booking->appendAdminPayload($booking->toCalendarPayload());

        return [
            'customer_name' => $booking->customer_full_name,
            'customer_phone' => null,
            'customer_email' => $booking->customer_email,
            'visit_count' => 0,
            'last_treatment' => null,
            'last_visit_date' => null,
            'customer_history_label' => null,
            'loyalty_tier_name' => null,
            'user_id' => null,
            'user_admin_url' => null,
            'current_booking_id' => $booking->id,
            'visit_history' => [],
            'upcoming_bookings' => [$this->serializeBookingSummary($booking, $payload)],
            'reminder_bookings' => [],
        ];
    }


    /**
     * @return array{visit_count: int, last_treatment: ?string, last_visit_date: ?string}
     */
    private function visitStats(string $normalizedPhone): array
    {
        $base = TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->with('product');

        $visitCount = (clone $base)->count();

        $last = (clone $base)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->first();

        return [
            'visit_count' => $visitCount,
            'last_treatment' => $last?->product?->name,
            'last_visit_date' => $last?->appointment_date?->format('d M Y'),
        ];
    }


    /**
     * @param  array{visit_count: int, last_treatment: ?string, last_visit_date: ?string}  $stats
     */
    private function historyLabel(array $stats): ?string
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


    /**
     * @return array<int, array<string, mixed>>
     */
    private function visitHistory(string $normalizedPhone, int $limit = 10): array
    {
        return TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->with(['product', 'beautician'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeBookingSummary($booking))
            ->values()
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function upcomingBookings(string $normalizedPhone, int $limit = 6): array
    {
        return TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->whereDate('appointment_date', '>=', today())
            ->with(['product', 'beautician'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->map(fn (TreatmentBooking $booking) => $this->serializeBookingSummary(
                $booking,
                $booking->appendAdminPayload($booking->toCalendarPayload())
            ))
            ->values()
            ->all();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function reminderBookings(string $normalizedPhone, int $limit = 5): array
    {
        return TreatmentBooking::query()
            ->matchingCustomerPhone($normalizedPhone)
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->whereNotNull('customer_phone')
            ->whereDate('appointment_date', '>=', today())
            ->with(['product', 'beautician'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit($limit)
            ->get()
            ->filter(fn (TreatmentBooking $booking) => $this->reminders->canSendReminder($booking))
            ->map(fn (TreatmentBooking $booking) => array_merge(
                $this->serializeBookingSummary($booking),
                $this->reminders->reminderMeta($booking),
            ))
            ->values()
            ->all();
    }


    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    private function serializeBookingSummary(TreatmentBooking $booking, ?array $payload = null): array
    {
        $payload ??= $booking->toCalendarPayload();

        return [
            'id' => $booking->id,
            'status' => $booking->status,
            'status_label' => TrLang::trans('admin.kanban.' . $booking->status),
            'appointment_date' => $booking->appointment_date?->format('d M Y'),
            'appointment_time' => $booking->appointment_time_range ?? $booking->appointment_time,
            'treatment_name' => $payload['treatment_name'] ?? $booking->product?->name ?? '—',
            'beautician_name' => $payload['beautician_name'] ?? $booking->beautician?->name,
            'total_formatted' => $payload['total_formatted'] ?? null,
            'order_url' => $payload['order_url'] ?? null,
        ];
    }


    private function userForPhone(string $normalizedPhone): ?User
    {
        $variants = PhoneNumber::variants($normalizedPhone);

        if ($variants === []) {
            return null;
        }

        return User::query()
            ->whereIn('phone', $variants)
            ->first();
    }


    private function loyaltyTierForUser(?User $user): ?string
    {
        if (! $user || ! is_module_enabled('Loyalty')) {
            return null;
        }

        $wallet = LoyaltyWallet::query()
            ->with('tier')
            ->where('user_id', $user->id)
            ->first();

        return $wallet?->tier?->translatedName();
    }
}
