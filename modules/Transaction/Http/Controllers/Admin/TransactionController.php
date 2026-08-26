<?php

namespace Modules\Transaction\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Order\Entities\Order;
use Modules\Transaction\Entities\Transaction;

class TransactionController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'transaction::transactions.transaction';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'transaction::admin.transactions';


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        return view("{$this->viewPath}.index", [
            'stats' => $this->transactionStats(),
        ]);
    }


    /**
     * @return array{offline: int, online: int, today: int, week: int, total: int}
     */
    private function transactionStats(): array
    {
        $offlineMethods = CheckoutCompletionGuard::offlineMethods();

        $offline = Order::query()->whereIn('payment_method', $offlineMethods)->count();
        $online = Order::query()
            ->where(function ($builder) use ($offlineMethods): void {
                $builder
                    ->whereNotIn('payment_method', $offlineMethods)
                    ->orWhereNull('payment_method')
                    ->orWhere('payment_method', '');
            })
            ->count();

        return [
            'offline' => $offline,
            'online' => $online,
            'total' => $offline + $online,
            'today' => Order::query()->whereDate('created_at', now()->toDateString())->count(),
            'week' => Order::query()->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
