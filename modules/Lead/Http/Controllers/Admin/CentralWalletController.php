<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralWalletService;
use Modules\Loyalty\Entities\LoyaltyWallet;

final class CentralWalletController
{
    public function __construct(
        private readonly CentralWalletService $wallets,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $customerId = $request->query('customer_id');
        if ($customerId === null || $customerId === '' || $customerId === 'all') {
            $customerId = null;
        } else {
            $customerId = (int) $customerId;
            if ($customerId <= 0) {
                $customerId = null;
            }
        }

        $filters = [
            'q' => $request->query('q'),
            'segment' => $request->query('segment', 'all'),
            'tier' => $request->query('tier'),
            'customer_id' => $customerId,
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $paginator = $this->wallets->paginate($filters);

        $data = collect($paginator->items())
            ->map(fn (LoyaltyWallet $wallet) => $this->wallets->toArray($wallet))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->wallets->summary($filters),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => [
                'segments' => $this->wallets->segmentOptions(),
                'tiers' => $this->wallets->tierOptions(),
            ],
        ]);
    }
}
