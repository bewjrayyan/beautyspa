<?php

namespace Modules\Loyalty\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Loyalty\Support\LoyaltyLang;

class TierTable extends AdminTable
{
    public function make()
    {
        $currency = currency_symbol(setting('default_currency'));
        $activeLabel = LoyaltyLang::get('tiers.form.status_active');
        $inactiveLabel = LoyaltyLang::get('tiers.form.status_inactive');
        $editLabel = LoyaltyLang::get('tiers.index.edit');

        return $this->newTable()
            ->addColumn('name', function ($tier) {
                $url = route('admin.loyalty.tiers.edit', $tier);

                return '<a href="' . $url . '" class="loyalty-tier-table__name">'
                    . '<span class="loyalty-tier-table__pill loyalty-tier-table__pill--' . e($tier->slugThemeClass()) . '">'
                    . e($tier->translatedName())
                    . '</span></a>';
            })
            ->addColumn('min_spend', fn ($tier) => '<span class="loyalty-tier-table__rm">' . $currency . ' ' . number_format($tier->min_lifetime_spend, 2) . '</span>')
            ->addColumn('multiplier', fn ($tier) => '<span class="loyalty-tier-table__multiplier-badge">' . $tier->earn_multiplier . '×</span>')
            ->addColumn('members', fn ($tier) => '<span class="loyalty-tier-table__members">' . number_format($tier->wallets_count ?? 0) . '</span>')
            ->editColumn('status', function ($tier) use ($activeLabel, $inactiveLabel) {
                $class = $tier->is_active ? 'loyalty-tier-table__status--active' : 'loyalty-tier-table__status--inactive';

                return '<span class="loyalty-tier-table__status ' . $class . '">'
                    . e($tier->is_active ? $activeLabel : $inactiveLabel)
                    . '</span>';
            })
            ->addColumn('actions', function ($tier) use ($editLabel) {
                $url = route('admin.loyalty.tiers.edit', $tier);

                return '<a href="' . $url . '" class="btn btn-default btn-sm loyalty-tier-table__edit" onclick="event.stopPropagation()">'
                    . '<i class="fa fa-pencil" aria-hidden="true"></i> '
                    . e($editLabel)
                    . '</a>';
            })
            ->rawColumns(['name', 'min_spend', 'multiplier', 'members', 'status', 'actions']);
    }
}
