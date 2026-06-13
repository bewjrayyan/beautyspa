<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Support\PhoneNumber;

class CustomerLookupService
{
    /**
     * @return array<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }>
     */
    public function search(string $query, int $limit = 8): array
    {
        $query = trim($query);

        if (strlen($query) < 3) {
            return [];
        }

        $normalized = PhoneNumber::normalize($query);
        $digitsOnly = preg_replace('/\D+/', '', $query) ?? '';

        $bookings = TreatmentBooking::query()
            ->whereNotNull('customer_phone')
            ->where(function ($inner) use ($query, $normalized, $digitsOnly) {
                if ($digitsOnly !== '' && strlen($digitsOnly) >= 3) {
                    $inner->where('customer_phone', 'like', '%' . $digitsOnly . '%');
                }

                $inner->orWhere('customer_first_name', 'like', '%' . $query . '%')
                    ->orWhere('customer_last_name', 'like', '%' . $query . '%');

                if ($normalized !== '') {
                    $inner->orWhere(function ($phoneQuery) use ($normalized) {
                        $phoneQuery->matchingCustomerPhone($normalized);
                    });
                }
            })
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get([
                'customer_first_name',
                'customer_last_name',
                'customer_phone',
                'customer_email',
            ]);

        return $this->dedupeByPhone($bookings)
            ->take($limit)
            ->map(fn (TreatmentBooking $booking) => [
                'customer_first_name' => $booking->customer_first_name,
                'customer_last_name' => $booking->customer_last_name,
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
            ])
            ->values()
            ->all();
    }


    /**
     * @param Collection<int, TreatmentBooking> $bookings
     * @return Collection<int, TreatmentBooking>
     */
    private function dedupeByPhone(Collection $bookings): Collection
    {
        $seen = [];

        return $bookings->filter(function (TreatmentBooking $booking) use (&$seen) {
            $key = PhoneNumber::normalize($booking->customer_phone ?? '') ?: (string) $booking->customer_phone;

            if ($key === '' || isset($seen[$key])) {
                return false;
            }

            $seen[$key] = true;

            return true;
        });
    }
}
