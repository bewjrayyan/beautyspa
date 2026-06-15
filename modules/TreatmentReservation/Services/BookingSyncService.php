<?php

namespace Modules\TreatmentReservation\Services;

use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Support\PhoneNumber;

class BookingSyncService
{
    public static bool $syncingFromOrder = false;


    public function syncFromOrder(Order $order): ?TreatmentBooking
    {
        $order->loadMissing(['products.product']);

        $product = $this->resolveTreatmentProduct($order);

        if (! $product || (! $order->beautician_id && ! $order->appointment_date)) {
            $this->removeBookingForOrder($order);

            return null;
        }

        $existing = TreatmentBooking::withTrashed()
            ->where('order_id', $order->id)
            ->first();

        if ($existing?->trashed()) {
            $existing->restore();
        }

        $normalizedPhone = PhoneNumber::normalize($order->customer_phone);

        $data = [
            'beautician_id' => $order->beautician_id,
            'treatment_category_id' => $product->treatment_category_id,
            'product_id' => $product->id,
            'customer_first_name' => $order->customer_first_name,
            'customer_last_name' => $order->customer_last_name,
            'customer_phone' => $normalizedPhone !== '' ? $normalizedPhone : $order->customer_phone,
            'customer_email' => $order->customer_email,
            'appointment_date' => $order->appointment_date,
            'appointment_time' => $order->appointment_time,
            'total' => $order->total->amount(),
            'currency' => $order->currency,
            'notes' => $order->note,
            'payment_status' => $order->payment_status,
        ];

        if (! $existing) {
            $data['status'] = TreatmentBooking::statusFromOrder($order->status, $order->payment_status);
        }

        self::$syncingFromOrder = true;

        try {
            return TreatmentBooking::updateOrCreate(
                ['order_id' => $order->id],
                $data
            );
        } finally {
            self::$syncingFromOrder = false;
        }
    }


    public function syncAllOrders(): int
    {
        $count = 0;

        Order::query()
            ->whereNotNull('beautician_id')
            ->with(['products.product'])
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    if ($this->syncFromOrder($order)) {
                        $count++;
                    }
                }
            });

        return $count;
    }


    public function cleanupInvalidBookings(): int
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct(false)
            ->delete();
    }


    /**
     * Trash bookings linked to soft-deleted or missing orders (backfill + safety net).
     */
    public function trashBookingsWithoutActiveOrder(): int
    {
        $count = 0;

        TreatmentBooking::query()
            ->whereNotNull('order_id')
            ->whereDoesntHave('order')
            ->each(function (TreatmentBooking $booking) use (&$count) {
                if (! $booking->trashed()) {
                    $booking->delete();
                    $count++;
                }
            });

        return $count;
    }


    public function trashBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->delete());
    }


    public function restoreBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::onlyTrashed()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->restore());
    }


    public function forceDeleteBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::withTrashed()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->forceDelete());
    }


    private function removeBookingForOrder(Order $order): void
    {
        $this->trashBookingsForOrder($order);
    }


    private function resolveTreatmentProduct(Order $order): ?Product
    {
        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;

            if ($product && $product->is_virtual) {
                return $product;
            }
        }

        return null;
    }
}
