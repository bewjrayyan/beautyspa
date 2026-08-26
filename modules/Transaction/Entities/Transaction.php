<?php

namespace Modules\Transaction\Entities;

use Illuminate\Http\Request;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Order\Entities\Order;
use Modules\Payment\Facades\Gateway;
use Modules\Support\Eloquent\Model;
use Modules\Transaction\Admin\TransactionTable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'deleted_at' => 'datetime',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function getPaymentMethodAttribute($paymentMethod)
    {
        return Gateway::get($paymentMethod)->label ?? '';
    }


    public function table(Request $request)
    {
        $channel = $request->get('channel') === 'offline' ? 'offline' : 'online';
        $offlineMethods = CheckoutCompletionGuard::offlineMethods();

        $query = Order::query()
            ->select([
                'id',
                'customer_first_name',
                'customer_last_name',
                'payment_method',
                'payment_status',
                'beautician_id',
                'spa_branch_id',
                'currency',
                'total',
                'created_at',
            ])
            ->with([
                'transaction' => static fn ($relation) => $relation->withTrashed(),
                'beautician:id,first_name,last_name',
                'spaBranch:id,name',
            ]);

        if ($channel === 'offline') {
            $query->whereIn('payment_method', $offlineMethods);
        } else {
            // Catch-all so non-offline methods (and blank legacy rows) are never dropped.
            $query->where(function ($builder) use ($offlineMethods): void {
                $builder
                    ->whereNotIn('payment_method', $offlineMethods)
                    ->orWhereNull('payment_method')
                    ->orWhere('payment_method', '');
            });
        }

        return new TransactionTable($query);
    }
}
