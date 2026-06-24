<?php

namespace Modules\GoogleIntegration\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Order\Entities\Order;

class GoogleSheetsSyncLog extends Model
{
    protected $fillable = [
        'order_id',
        'trigger',
        'status',
        'sheet_tab',
        'message',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
