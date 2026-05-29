<?php

namespace Modules\Payment\Libraries\Chip;

use Exception;
use Illuminate\Support\Facades\Http;

class ChipCollectClient
{
    private const BASE_URL = 'https://gate.chip-in.asia/api/v1/';

    private const PAID_STATUSES = ['paid', 'success', 'completed', 'captured'];


    public function __construct(
        private readonly string $brandId,
        private readonly string $apiKey,
    ) {
    }


    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function createPurchase(array $payload): array
    {
        return $this->request('post', 'purchases/', $payload);
    }


    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getPurchase(string $purchaseId): array
    {
        return $this->request('get', 'purchases/' . $purchaseId . '/');
    }


    /**
     * @return array{
     *     available_payment_methods: list<string>,
     *     card_methods: list<string>,
     *     names: array<string, string>
     * }
     *
     * @throws Exception
     */
    public function listPaymentMethods(string $currency = 'MYR'): array
    {
        $response = $this->request('get', 'payment_methods/', [], [
            'brand_id' => $this->brandId,
            'currency' => $currency,
        ]);

        return [
            'available_payment_methods' => array_values($response['available_payment_methods'] ?? []),
            'card_methods' => array_values($response['card_methods'] ?? []),
            'names' => is_array($response['names'] ?? null) ? $response['names'] : [],
        ];
    }


    public function isPaid(array $purchase): bool
    {
        $status = strtolower((string) ($purchase['status'] ?? ''));

        return in_array($status, self::PAID_STATUSES, true);
    }


    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $query
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function request(string $method, string $uri, array $payload = [], array $query = []): array
    {
        $http = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->baseUrl(self::BASE_URL)
            ->timeout(30);

        $response = $method === 'get'
            ? $http->get($uri, $query !== [] ? $query : $payload)
            : $http->{$method}($uri, $payload);

        if ($response->failed()) {
            $message = $response->json('message')
                ?? $response->json('error')
                ?? $response->body();

            throw new Exception(trim((string) $message) ?: 'CHIP API request failed.');
        }

        return $response->json() ?? [];
    }
}
