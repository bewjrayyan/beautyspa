<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // pending_payment → pending (fulfillment); keep/set payment pending when empty
        DB::table('orders')
            ->where('status', 'pending_payment')
            ->update(['status' => 'pending']);

        DB::table('orders')
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->whereNull('payment_status')->orWhere('payment_status', '');
            })
            ->update(['payment_status' => 'pending']);

        // on_hold → pending
        DB::table('orders')
            ->where('status', 'on_hold')
            ->update(['status' => 'pending']);

        // refunded order status → canceled + payment refunded
        DB::table('orders')
            ->where('status', 'refunded')
            ->update([
                'status' => 'canceled',
                'payment_status' => 'refunded',
            ]);
    }

    public function down(): void
    {
        // Best-effort reverse: cannot perfectly restore original statuses.
        DB::table('orders')
            ->where('status', 'canceled')
            ->where('payment_status', 'refunded')
            ->update(['status' => 'refunded']);
    }
};
