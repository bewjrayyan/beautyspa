<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Services\LoyaltyWalletService;

class ExpireLoyaltyPointsCommand extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire loyalty points past their expiry date.';


    public function handle(LoyaltyWalletService $wallets): int
    {
        $expired = 0;

        LoyaltyTransaction::query()
            ->where('type', TransactionType::EARN)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->with('wallet')
            ->chunkById(100, function ($transactions) use ($wallets, &$expired) {
                foreach ($transactions as $earn) {
                    if (!$earn->wallet) {
                        continue;
                    }

                    $expireRef = $earn->id . ':expire';

                    if ($wallets->findExistingTransaction(
                        $earn->wallet,
                        TransactionType::EXPIRE,
                        'earn_tx',
                        $expireRef
                    )) {
                        continue;
                    }

                    $wallets->debit(
                        $earn->wallet,
                        $earn->points,
                        TransactionType::EXPIRE,
                        'earn_tx',
                        $expireRef,
                        'Points expired',
                        ['earn_transaction_id' => $earn->id]
                    );

                    $expired++;
                }
            });

        $this->info("Processed {$expired} point expiration(s).");

        return self::SUCCESS;
    }
}
