<?php

namespace Modules\Loyalty\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Loyalty\Support\MemberUserSearch;

class MemberTable extends AdminTable
{
    public function make()
    {
        $currency = currency_symbol(setting('default_currency'));

        return $this->newTable()
            ->filterColumn('customer', function ($query, $keyword) {
                $query->whereHas('user', function ($userQuery) use ($keyword) {
                    MemberUserSearch::apply($userQuery, $keyword);
                });
            })
            ->addColumn('customer', function ($wallet) {
                $user = $wallet->user;

                if (! $user) {
                    return '<span class="text-muted">—</span>';
                }

                $name = e(trim($user->first_name . ' ' . $user->last_name));
                $email = e($user->email);
                $initial = e(mb_strtoupper(mb_substr($user->first_name ?: $user->email, 0, 1)));

                return '<div class="loyalty-members-table__customer">'
                    . '<span class="loyalty-members-table__avatar">' . $initial . '</span>'
                    . '<span class="loyalty-members-table__identity">'
                    . '<strong class="loyalty-members-table__name">' . $name . '</strong>'
                    . '<small class="loyalty-members-table__email">' . $email . '</small>'
                    . '</span></div>';
            })
            ->addColumn('tier', function ($wallet) {
                if (! $wallet->tier) {
                    return '<span class="text-muted">—</span>';
                }

                return '<span class="loyalty-members-table__tier">'
                    . '<i class="fa fa-star" aria-hidden="true"></i> '
                    . e($wallet->tier->translatedName())
                    . '</span>';
            })
            ->addColumn('balance', function ($wallet) {
                return '<span class="loyalty-members-table__points">'
                    . number_format($wallet->balance)
                    . '</span>';
            })
            ->addColumn('lifetime_spend', function ($wallet) use ($currency) {
                return '<span class="loyalty-members-table__spend">'
                    . $currency . ' ' . number_format($wallet->lifetime_spend, 2)
                    . '</span>';
            })
            ->addColumn('actions', function ($wallet) {
                $url = route('admin.loyalty.members.show', $wallet);

                return '<a href="' . $url . '" class="btn btn-primary btn-sm loyalty-members-table__view" onclick="event.stopPropagation()">'
                    . '<i class="fa fa-eye" aria-hidden="true"></i> '
                    . e(trans('loyalty::members.view'))
                    . '</a>';
            })
            ->rawColumns(['customer', 'tier', 'balance', 'lifetime_spend', 'actions']);
    }
}
