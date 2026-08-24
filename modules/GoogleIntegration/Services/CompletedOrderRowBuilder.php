<?php

namespace Modules\GoogleIntegration\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class CompletedOrderRowBuilder
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return GoogleSheetsColumnConfig::headerLabelsForStatus(Order::COMPLETED);
    }


    public function headersForStatus(string $status): array
    {
        return GoogleSheetsColumnConfig::headerLabelsForStatus($status);
    }


    /**
     * @return array<int, string|int|float|null>
     */
    public function row(Order $order): array
    {
        $values = $this->valueMap($order);

        return array_map(
            fn (string $key) => $values[$key] ?? '',
            GoogleSheetsColumnConfig::enabledKeysForStatus($order->status),
        );
    }


    /**
     * @return array<string, string|int|float|null>
     */
    private function valueMap(Order $order): array
    {
        $order->loadMissing(['products', 'coupon', 'beautician', 'spaBranch']);

        $treatmentBookings = is_module_enabled('TreatmentReservation')
            ? TreatmentBooking::query()
                ->where('order_id', $order->id)
                ->with(['product', 'beautician'])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->orderBy('id')
                ->get()
            : collect();

        $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name);
        $treatments = $order->products
            ->map(fn (OrderProduct $product) => $product->nameWithSelections() . ' (x' . $product->qty . ')')
            ->implode('; ');

        return [
            'order_id' => (string) $order->id,
            'order_date' => $order->created_at->format('Y-m-d H:i:s'),
            'status' => $order->status(),
            'payment_status' => $order->paymentStatusLabel(),
            'treatment_status' => $this->bookingValues(
                $treatmentBookings,
                fn (TreatmentBooking $booking) => $booking->treatmentStatusLabel()
            ),
            'customer_name' => $customerName,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'beautician' => $order->beautician?->name ?? $this->noteValue($order, 'beautician') ?? '',
            'beautician_phone' => $order->beautician?->phone ?? '',
            'appointment_date' => $order->appointment_date?->format('Y-m-d') ?? ($this->noteValue($order, 'appointment_date') ?? ''),
            'appointment_time' => $order->appointment_time ?? ($this->noteValue($order, 'appointment_time') ?? ''),
            'treatments' => $treatments,
            'subtotal' => $order->sub_total->convertToCurrentCurrency()->amount(),
            'discount' => $order->discount->convertToCurrentCurrency()->amount(),
            'shipping' => $order->shipping_cost->convertToCurrentCurrency()->amount(),
            'tax' => $order->tax->convertToCurrentCurrency()->amount(),
            'total' => $order->total->convertToCurrentCurrency()->amount(),
            'payment_method' => $order->payment_method ?? '',
            'coupon' => $order->coupon?->code ?? '',
            'order_note' => $order->note ?? '',
            'synced_at' => now()->format('Y-m-d H:i:s'),
            'spa_branch' => $order->spaBranch?->name ?? '',
            'treatment_work_log_at' => $this->bookingValues(
                $treatmentBookings,
                fn (TreatmentBooking $booking) => $booking->beautician_notes_at?->format('Y-m-d H:i')
            ),
            'treatment_checklist_progress' => $this->bookingValues(
                $treatmentBookings,
                fn (TreatmentBooking $booking) => $booking->workLogProgressLabel()
            ),
            'beautician_notes' => Str::limit($this->bookingValues(
                $treatmentBookings,
                fn (TreatmentBooking $booking) => filled($booking->beautician_notes)
                    ? trim((string) $booking->beautician_notes)
                    : null
            ), 10000, '…'),
        ];
    }


    /**
     * @param Collection<int, TreatmentBooking> $bookings
     */
    private function bookingValues(Collection $bookings, callable $value): string
    {
        return $bookings
            ->map(function (TreatmentBooking $booking) use ($value) {
                $resolved = $value($booking);

                return filled($resolved) ? "#{$booking->id}: {$resolved}" : null;
            })
            ->filter()
            ->implode("\n");
    }


    private function noteValue(Order $order, string $field): ?string
    {
        $note = (string) $order->note;

        if ($note === '') {
            return null;
        }

        $patterns = [
            'beautician' => '/Beautician\s*:\s*(.+)/i',
            'appointment_date' => '/Appt\.?\s*Date\s*:\s*(.+)/i',
            'appointment_time' => '/Appt\.?\s*Time\s*:\s*(.+)/i',
        ];

        if (! isset($patterns[$field])) {
            return null;
        }

        if (preg_match($patterns[$field], $note, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
