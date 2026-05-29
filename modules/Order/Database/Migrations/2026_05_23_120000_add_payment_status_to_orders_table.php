<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Order\Entities\Order;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)
                ->default('pending')
                ->after('status');
        });

        DB::table('orders')->where('status', 'paid')->update([
            'status' => Order::COMPLETED,
            'payment_status' => 'paid',
        ]);

        $paidOrderIds = DB::table('transactions')
            ->whereNotNull('transaction_id')
            ->pluck('order_id');

        if ($paidOrderIds->isNotEmpty()) {
            DB::table('orders')
                ->whereIn('id', $paidOrderIds)
                ->update(['payment_status' => 'paid']);
        }

        DB::table('orders')
            ->where('status', Order::PENDING_PAYMENT)
            ->update(['payment_status' => 'pending']);

        DB::table('orders')
            ->where('status', Order::CANCELED)
            ->update(['payment_status' => 'canceled']);
    }


    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
