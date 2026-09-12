<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->discardStaleLaravelJobs();
        $this->cancelStaleOutboundMessages();
    }


    public function down(): void
    {
    }


    private function discardStaleLaravelJobs(): void
    {
        if (! Schema::hasTable('jobs')) {
            return;
        }

        $notificationJobs = [
            'Modules\\Checkout\\Listeners\\SendNewOrderSms',
            'Modules\\Order\\Listeners\\SendBankTransferPaymentProofWhatsApp',
            'Modules\\Order\\Listeners\\SendOrderStatusChangedSms',
            'Modules\\Order\\Listeners\\SendCompletedOrderGroupWhatsApp',
            'Modules\\Order\\Listeners\\SendCompletedOrderBeauticianWhatsApp',
            'Modules\\User\\Jobs\\ProcessOneSenderOutboundMessage',
        ];
        $cutoff = now()->subDay()->timestamp;

        DB::table('jobs')
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use ($notificationJobs): void {
                $ids = [];

                foreach ($jobs as $job) {
                    $payload = json_decode((string) $job->payload, true);

                    if (in_array($payload['displayName'] ?? null, $notificationJobs, true)) {
                        $ids[] = $job->id;
                    }
                }

                if ($ids !== []) {
                    DB::table('jobs')->whereIn('id', $ids)->delete();
                }
            });
    }


    private function cancelStaleOutboundMessages(): void
    {
        if (! Schema::hasTable('onesender_outbound_queue')) {
            return;
        }

        DB::table('onesender_outbound_queue')
            ->whereIn('status', ['pending', 'processing'])
            ->where(function ($query): void {
                $query->where(function ($media): void {
                    $media->whereIn('message_type', ['image', 'document'])
                        ->where('created_at', '<=', now()->subMinutes(75));
                })->orWhere(function ($text): void {
                    $text->where('message_type', 'text')
                        ->where('created_at', '<=', now()->subDay());
                });
            })
            ->update([
                'status' => 'cancelled',
                'processing_at' => null,
                'cancelled_at' => now(),
                'active_dedupe_key' => null,
                'error_message' => 'Expired before delivery; message was not sent.',
            ]);
    }
};
