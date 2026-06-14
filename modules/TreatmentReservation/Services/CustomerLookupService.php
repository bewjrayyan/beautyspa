<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Loyalty\Support\MemberUserSearch;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;
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

        $results = collect()
            ->merge($this->searchTreatmentBookings($query))
            ->merge($this->searchOrders($query))
            ->merge($this->searchUsers($query));

        return $this->dedupeCustomers($results)
            ->take($limit)
            ->values()
            ->all();
    }


    /**
     * @return Collection<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }>
     */
    private function searchTreatmentBookings(string $query): Collection
    {
        return TreatmentBooking::query()
            ->where(fn (Builder $inner) => $this->applyCustomerSearchConditions($inner, $query))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get([
                'customer_first_name',
                'customer_last_name',
                'customer_phone',
                'customer_email',
            ])
            ->map(fn (TreatmentBooking $booking) => $this->mapCustomerRecord(
                $booking->customer_first_name,
                $booking->customer_last_name,
                $booking->customer_phone,
                $booking->customer_email,
            ));
    }


    /**
     * @return Collection<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }>
     */
    private function searchOrders(string $query): Collection
    {
        return Order::query()
            ->where(fn (Builder $inner) => $this->applyCustomerSearchConditions($inner, $query))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get([
                'customer_first_name',
                'customer_last_name',
                'customer_phone',
                'customer_email',
            ])
            ->map(fn (Order $order) => $this->mapCustomerRecord(
                $order->customer_first_name,
                $order->customer_last_name,
                $order->customer_phone,
                $order->customer_email,
            ));
    }


    /**
     * @return Collection<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }>
     */
    private function searchUsers(string $query): Collection
    {
        $customerRoleId = setting('customer_role');

        if (! $customerRoleId) {
            return collect();
        }

        return User::query()
            ->whereHas(
                'roles',
                fn (Builder $roleQuery) => $roleQuery->where('id', $customerRoleId)
            )
            ->where(fn (Builder $inner) => MemberUserSearch::apply($inner, $query))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get([
                'first_name',
                'last_name',
                'phone',
                'email',
            ])
            ->map(fn (User $user) => $this->mapCustomerRecord(
                $user->first_name,
                $user->last_name,
                $user->phone,
                $user->email,
            ));
    }


    private function applyCustomerSearchConditions(Builder $query, string $search): void
    {
        $like = '%' . $search . '%';
        $normalizedPhone = PhoneNumber::normalize($search);
        $digitsOnly = preg_replace('/\D+/', '', $search) ?? '';
        $normalizedEmail = strtolower($search);

        $query->where(function (Builder $inner) use ($like, $normalizedPhone, $digitsOnly, $normalizedEmail) {
            $inner->where('customer_first_name', 'like', $like)
                ->orWhere('customer_last_name', 'like', $like)
                ->orWhereRaw(
                    "TRIM(CONCAT(COALESCE(customer_first_name, ''), ' ', COALESCE(customer_last_name, ''))) LIKE ?",
                    [$like]
                )
                ->orWhereRaw('LOWER(customer_email) LIKE ?', ['%' . $normalizedEmail . '%']);

            if ($digitsOnly !== '' && strlen($digitsOnly) >= 3) {
                $inner->orWhere('customer_phone', 'like', '%' . $digitsOnly . '%');
            }

            if ($normalizedPhone !== '') {
                $inner->orWhere(function (Builder $phoneQuery) use ($normalizedPhone) {
                    $this->applyPhoneMatch($phoneQuery, $normalizedPhone, 'customer_phone');
                });
            }
        });
    }


    private function applyPhoneMatch(Builder $query, string $normalizedPhone, string $column): void
    {
        $variants = PhoneNumber::variants($normalizedPhone);

        if ($variants === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $inner) use ($variants, $column) {
            foreach ($variants as $variant) {
                $inner->orWhere($column, $variant);
            }
        });
    }


    /**
     * @return array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }
     */
    private function mapCustomerRecord(
        ?string $firstName,
        ?string $lastName,
        ?string $phone,
        ?string $email,
    ): array {
        return [
            'customer_first_name' => trim((string) $firstName),
            'customer_last_name' => trim((string) $lastName),
            'customer_phone' => filled($phone) ? (string) $phone : null,
            'customer_email' => filled($email) ? (string) $email : null,
        ];
    }


    /**
     * @param Collection<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }> $customers
     * @return Collection<int, array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * }>
     */
    private function dedupeCustomers(Collection $customers): Collection
    {
        $seen = [];

        return $customers
            ->filter(function (array $customer) use (&$seen) {
                if ($customer['customer_first_name'] === '' && $customer['customer_last_name'] === '') {
                    return false;
                }

                $key = $this->customerKey($customer);

                if ($key === '' || isset($seen[$key])) {
                    return false;
                }

                $seen[$key] = true;

                return true;
            })
            ->values();
    }


    /**
     * @param array{
     *     customer_first_name: string,
     *     customer_last_name: string,
     *     customer_phone: string|null,
     *     customer_email: string|null
     * } $customer
     */
    private function customerKey(array $customer): string
    {
        $phone = PhoneNumber::normalize($customer['customer_phone'] ?? '');

        if ($phone !== '') {
            return 'phone:' . $phone;
        }

        $email = strtolower(trim((string) ($customer['customer_email'] ?? '')));

        if ($email !== '') {
            return 'email:' . $email;
        }

        return 'name:' . strtolower(trim($customer['customer_first_name'] . '|' . $customer['customer_last_name']));
    }
}
