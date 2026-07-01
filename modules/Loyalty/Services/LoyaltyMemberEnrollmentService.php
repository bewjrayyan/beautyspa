<?php

namespace Modules\Loyalty\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\User\Entities\User;

class LoyaltyMemberEnrollmentService
{
    public function __construct(private LoyaltyWalletService $wallets) {}


    public function countMissing(bool $customersOnly = true): int
    {
        return $this->missingUsersQuery($customersOnly)->count();
    }


    /**
     * @return array{enrolled: int, skipped: int}
     */
    public function enrollMissing(
        bool $customersOnly = true,
        ?int $limit = null,
        ?callable $onProgress = null
    ): array {
        $query = $this->missingUsersQuery($customersOnly)->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $enrolled = 0;

        $query->chunkById(200, function ($users) use (&$enrolled, $onProgress) {
            foreach ($users as $user) {
                $this->wallets->getOrCreateForUser($user);
                $enrolled++;

                if ($onProgress) {
                    $onProgress($user, $enrolled);
                }
            }
        });

        return ['enrolled' => $enrolled, 'skipped' => 0];
    }


    /**
     * @param array<int, int|string> $userIds
     *
     * @return array{enrolled: int, skipped: int}
     */
    public function enrollUserIds(array $userIds, bool $customersOnly = true): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if ($userIds === []) {
            return ['enrolled' => 0, 'skipped' => 0];
        }

        $existingWalletIds = LoyaltyWallet::query()
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();

        $enrolled = 0;
        $skipped = 0;

        User::query()
            ->whereIn('id', $userIds)
            ->with('roles')
            ->orderBy('id')
            ->each(function (User $user) use ($customersOnly, $existingWalletIds, &$enrolled, &$skipped) {
                if ($customersOnly && ! $user->isCustomer()) {
                    $skipped++;

                    return;
                }

                if (in_array($user->id, $existingWalletIds, true)) {
                    $skipped++;

                    return;
                }

                $this->wallets->getOrCreateForUser($user);
                $enrolled++;
            });

        return ['enrolled' => $enrolled, 'skipped' => $skipped];
    }


    private function missingUsersQuery(bool $customersOnly): Builder
    {
        $query = User::query()
            ->whereNotIn('id', LoyaltyWallet::query()->select('user_id'));

        if ($customersOnly) {
            $customerRoleId = setting('customer_role');

            if ($customerRoleId) {
                $query->whereHas('roles', function ($roleQuery) use ($customerRoleId) {
                    $roleQuery->where('roles.id', $customerRoleId);
                });
            }
        }

        return $query;
    }
}
