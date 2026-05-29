<?php

namespace Modules\Report;

abstract class BeauticianAwareReport extends Report
{
    protected $filters = ['from', 'to', 'status', 'group', 'beautician_id'];

    protected $date = 'orders.created_at';

    protected function beautician_id($beauticianId): void
    {
        if ($beauticianId !== '' && $beauticianId !== null) {
            $this->query->where('orders.beautician_id', $beauticianId);
        }
    }

    protected function beauticiansForFilter()
    {
        if (!class_exists(\Modules\Beautician\Entities\Beautician::class)) {
            return collect();
        }

        return \Modules\Beautician\Entities\Beautician::namesForFilter();
    }
}
