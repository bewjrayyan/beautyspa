<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Order\Entities\Order;

class OrderPaymentReferenceService
{
    /**
     * Statuses that require a bank reference for offline reconciliation.
     *
     * @return list<string>
     */
    public static function statusesRequiringReference(): array
    {
        return [
            Order::PAYMENT_PAID,
            Order::PAYMENT_PROCESSING,
        ];
    }

    public static function orderNeedsPaymentReference(Order $order): bool
    {
        return (string) $order->getRawOriginal('payment_method') === 'bank_transfer';
    }

    /**
     * Persist admin bank reference onto the order transaction row.
     *
     * @throws ValidationException
     */
    public function syncForPaymentStatus(
        Order $order,
        string $paymentStatus,
        ?string $transactionId = null,
        ?string $adminNote = null,
    ): void {
        if (! self::orderNeedsPaymentReference($order)) {
            return;
        }

        $transactionId = $this->normalizeOptionalString($transactionId);
        $adminNote = $this->normalizeOptionalString($adminNote);

        $existingId = trim((string) ($order->transaction?->getRawOriginal('transaction_id')
            ?? $order->transaction?->transaction_id
            ?? ''));

        $resolvedId = $transactionId !== null && $transactionId !== ''
            ? $transactionId
            : ($existingId !== '' ? $existingId : null);

        if (
            in_array($paymentStatus, self::statusesRequiringReference(), true)
            && ($resolvedId === null || $resolvedId === '')
        ) {
            throw ValidationException::withMessages([
                'transaction_id' => [trans('order::messages.payment_reference_required')],
            ]);
        }

        // Nothing to write and nothing required.
        if (($resolvedId === null || $resolvedId === '') && ($adminNote === null || $adminNote === '')) {
            return;
        }

        Validator::make(
            [
                'transaction_id' => $resolvedId,
                'admin_note' => $adminNote,
            ],
            [
                'transaction_id' => ['nullable', 'string', 'max:191'],
                'admin_note' => ['nullable', 'string', 'max:2000'],
            ]
        )->validate();

        $paymentMethod = (string) $order->getRawOriginal('payment_method');

        $attributes = [
            'payment_method' => $paymentMethod !== '' ? $paymentMethod : 'bank_transfer',
        ];

        if ($resolvedId !== null && $resolvedId !== '') {
            $attributes['transaction_id'] = $resolvedId;
        }

        if ($adminNote !== null) {
            $attributes['admin_note'] = $adminNote !== '' ? $adminNote : null;
        }

        // updateOrCreate requires transaction_id (non-null column). Keep existing if only note changes.
        if (! isset($attributes['transaction_id'])) {
            if ($existingId === '') {
                return;
            }

            $attributes['transaction_id'] = $existingId;
        }

        $transaction = $order->transaction()->withTrashed()->updateOrCreate(
            ['order_id' => $order->id],
            $attributes
        );

        if ($transaction->trashed()) {
            $transaction->restore();
        }

        $order->unsetRelation('transaction');
        $order->load('transaction');
    }

    private function normalizeOptionalString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? '' : $trimmed;
    }
}
