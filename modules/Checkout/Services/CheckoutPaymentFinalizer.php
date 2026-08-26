<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Payment\HasTransactionReference;
use Modules\TreatmentReservation\Services\BookingSyncService;

class CheckoutPaymentFinalizer
{
    public function finalize(Order $order, string $paymentMethod, mixed $response): Order
    {
        return DB::transaction(function () use ($order, $paymentMethod, $response): Order {
            $lockedOrder = Order::query()
                ->withTrashed()
                ->with('transaction')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isMatchingPaidReplay($lockedOrder, $paymentMethod, $response)) {
                $this->ensureDeferredTreatmentBookings($lockedOrder);

                return $lockedOrder;
            }

            CheckoutCompletionGuard::assertCanComplete($lockedOrder, $paymentMethod);

            $previousStatus = $lockedOrder->status;
            $lockedOrder->storeTransaction($response);
            $lockedOrder->load('transaction');

            $isPaid = filled($lockedOrder->transaction?->transaction_id);

            return BookingSyncService::withoutOrderObserverSync(function () use (
                $lockedOrder,
                $isPaid,
                $previousStatus,
            ): Order {
                $lockedOrder->update([
                    'status' => $isPaid ? Order::COMPLETED : Order::PENDING,
                    'payment_status' => $isPaid ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING,
                ]);

                if ($isPaid && app('modules')->isEnabled('TreatmentReservation')) {
                    $holdService = app(\Modules\TreatmentReservation\Services\CheckoutSlotHoldService::class);
                    $holdService->extendHoldsForOrder((int) $lockedOrder->id);

                    app(BookingSyncService::class)
                        ->syncPendingCheckoutLinesAfterPayment($lockedOrder->fresh(['products.product']));

                    $holdService->releaseHoldsForOrder((int) $lockedOrder->id);
                }

                DB::afterCommit(function () use ($lockedOrder, $previousStatus, $isPaid): void {
                    try {
                        event(new OrderPlaced($lockedOrder));

                        if ($isPaid && $previousStatus !== Order::COMPLETED) {
                            event(new OrderStatusChanged($lockedOrder, $isPaid ? 'order_and_payment' : 'order', null, $lockedOrder->status));
                        }
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                });

                return $lockedOrder;
            });
        }, 3);
    }


    private function isMatchingPaidReplay(Order $order, string $paymentMethod, mixed $response): bool
    {
        if (! $order->isPaymentPaid() || ! $response instanceof HasTransactionReference) {
            return false;
        }

        return hash_equals((string) $order->getRawOriginal('payment_method'), $paymentMethod)
            && hash_equals(
                (string) $order->transaction?->transaction_id,
                (string) $response->getTransactionReference()
            );
    }


    private function ensureDeferredTreatmentBookings(Order $order): void
    {
        if (! app('modules')->isEnabled('TreatmentReservation')) {
            return;
        }

        if (! BookingSyncService::shouldDeferUntilPayment($order)) {
            return;
        }

        if ($order->treatmentBookings()->exists()) {
            return;
        }

        BookingSyncService::withoutOrderObserverSync(function () use ($order): void {
            $holdService = app(\Modules\TreatmentReservation\Services\CheckoutSlotHoldService::class);
            $holdService->extendHoldsForOrder((int) $order->id);

            app(BookingSyncService::class)
                ->syncPendingCheckoutLinesAfterPayment($order->fresh(['products.product']));

            $holdService->releaseHoldsForOrder((int) $order->id);
        });
    }
}
