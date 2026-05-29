<?php

namespace Modules\Transaction\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Traits\HasCrudActions;
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
     * @return array{total: int, today: int, week: int}
     */
    private function transactionStats(): array
    {
        return [
            'total' => Transaction::count(),
            'today' => Transaction::whereDate('created_at', now()->toDateString())->count(),
            'week' => Transaction::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
