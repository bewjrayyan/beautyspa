<?php

namespace Modules\Report\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Beautician\Entities\Beautician;

trait JoinsOrderReportDetails
{
    protected function applyOrderReportDetailJoins(Builder $query, string $orderTable = 'orders'): Builder
    {
        if (! $this->queryHasJoin($query, 'beauticians')) {
            $query->leftJoin('beauticians', "{$orderTable}.beautician_id", '=', 'beauticians.id');
        }

        return $query;
    }

    protected function addOrderReportDetailSelects(Builder $query, string $orderTable = 'orders'): Builder
    {
        return $query->addSelect([
            "{$orderTable}.id as order_id",
            "{$orderTable}.created_at as order_date",
            "{$orderTable}.customer_first_name",
            "{$orderTable}.customer_last_name",
            "{$orderTable}.customer_email",
            "{$orderTable}.customer_phone",
            "{$orderTable}.appointment_date",
            "{$orderTable}.appointment_time",
            "{$orderTable}.status as order_status",
            "{$orderTable}.payment_status",
            "{$orderTable}.payment_method",
        ])->selectRaw(Beautician::sqlFullName('beauticians') . ' as beautician_name');
    }

    private function queryHasJoin(Builder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins ?? [];

        foreach ($joins as $join) {
            if (str_contains($join->table, $table)) {
                return true;
            }
        }

        return false;
    }
}
