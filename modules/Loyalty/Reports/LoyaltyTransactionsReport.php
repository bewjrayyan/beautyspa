<?php

namespace Modules\Loyalty\Reports;

use Modules\Report\Report;
use Modules\Loyalty\Entities\LoyaltyTransaction;

class LoyaltyTransactionsReport extends Report
{
    protected $date = 'loyalty_transactions.created_at';

    protected $filters = ['from', 'to'];


    protected function view()
    {
        return 'loyalty::admin.reports.transactions';
    }


    protected function query()
    {
        return LoyaltyTransaction::query()
            ->select(
                'loyalty_transactions.id',
                'loyalty_transactions.type',
                'loyalty_transactions.points',
                'loyalty_transactions.balance_after',
                'loyalty_transactions.description',
                'loyalty_transactions.created_at',
                'users.email as customer_email',
                'users.first_name',
                'users.last_name'
            )
            ->join('loyalty_wallets', 'loyalty_wallets.id', '=', 'loyalty_transactions.wallet_id')
            ->join('users', 'users.id', '=', 'loyalty_wallets.user_id')
            ->when(request('type'), function ($q, $type) {
                $q->where('loyalty_transactions.type', $type);
            })
            ->orderByDesc('loyalty_transactions.created_at');
    }


    protected function data()
    {
        return [
            'types' => [
                'earn' => trans('loyalty::reports.types.earn'),
                'redeem' => trans('loyalty::reports.types.redeem'),
                'adjust' => trans('loyalty::reports.types.adjust'),
                'expire' => trans('loyalty::reports.types.expire'),
                'clawback' => trans('loyalty::reports.types.clawback'),
                'bonus' => trans('loyalty::reports.types.bonus'),
            ],
        ];
    }
}
