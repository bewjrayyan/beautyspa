<?php

namespace Modules\Report\Concerns;

use Illuminate\Support\Collection;
use Modules\SpaBranch\Entities\SpaBranch;

trait FiltersBySpaBranch
{
    protected function spa_branch_id($branchId): void
    {
        if ($branchId !== '' && $branchId !== null) {
            $this->query->where('orders.spa_branch_id', $branchId);
        }
    }

    protected function spaBranchesForFilter(): Collection
    {
        if (! is_module_enabled('SpaBranch')) {
            return collect();
        }

        return SpaBranch::namesForFilter();
    }
}
