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
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getPublicKey(): array
    {
        return $this->request('get', 'public_key/');
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
            throw new Exception($this->formatErrorMessage($response));
        }

        return $response->json() ?? [];
    }


    private function formatErrorMessage($response): string
    {
        $body = $response->json();

        if (is_string($body)) {
            return trim($body) ?: 'CHIP API request failed.';
        }

        if (! is_array($body)) {
            return trim((string) $response->body()) ?: 'CHIP API request failed.';
        }

        foreach (['message', 'error', 'detail'] as $key) {
            if (! empty($body[$key]) && is_string($body[$key])) {
                return trim($body[$key]);
            }
        }

        $flattened = $this->flattenChipErrors($body);

        if ($flattened !== []) {
            return implode(' ', $flattened);
        }

        return trim((string) $response->body()) ?: 'CHIP API request failed.';
    }


    /**
     * @return array<int, string>
     */
    private function flattenChipErrors(array $data, string $prefix = ''): array
    {
        $messages = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                if (isset($value['message']) && is_string($value['message'])) {
                    $messages[] = trim($value['message']);

                    continue;
                }

                if (array_is_list($value) && isset($value[0]['message']) && is_string($value[0]['message'])) {
                    $messages[] = trim($value[0]['message']);

                    continue;
                }

                $messages = array_merge($messages, $this->flattenChipErrors($value, $path));

                continue;
            }

            if (is_string($value) && $value !== '') {
                $messages[] = trim($value);
            }
        }

        return array_values(array_unique(array_filter($messages)));
    }
}
