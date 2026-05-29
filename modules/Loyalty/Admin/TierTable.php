<?php

namespace Modules\Loyalty\Admin;

use Modules\Admin\Ui\AdminTable;

class TierTable extends AdminTable
{
    public function make()
    {
        $currency = currency_symbol(setting('default_currency'));

        return $this->newTable()
            ->addColumn('name', function ($tier) {
                $url = route('admin.loyalty.tiers.edit', $tier);

                return '<a href="' . $url . '" class="loyalty-tier-table__name"><strong>' . e($tier->name) . '</strong></a>'
                    . '<br><code class="loyalty-tier-table__slug">' . e($tier->slug) . '</code>';
            })
            ->addColumn('min_spend', fn ($tier) => '<span class="loyalty-tier-table__rm">' . $currency . ' ' . number_format($tier->min_lifetime_spend, 2) . '</span>')
            ->addColumn('multiplier', fn ($tier) => '<span class="loyalty-tier-table__multiplier">' . $tier->earn_multiplier . '×</span>')
            ->rawColumns(['name', 'min_spend', 'multiplier']);
    }
}
