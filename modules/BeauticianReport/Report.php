<?php

namespace Modules\BeauticianReport;

use Modules\Report\Report as BaseReport;

abstract class Report extends BaseReport
{
    protected $date = 'orders.created_at';

    protected $filters = ['from', 'to', 'status', 'group', 'beautician_id'];

    protected function beautician_id($beauticianId): void
    {
        if ($beauticianId !== '' && $beauticianId !== null) {
            $this->query->where('orders.beautician_id', $beauticianId);
        }
    }
}
