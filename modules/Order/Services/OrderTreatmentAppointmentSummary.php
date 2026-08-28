<?php

namespace Modules\Order\Services;

use Illuminate\Support\Collection;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class OrderTreatmentAppointmentSummary
{
    /**
     * @return Collection<int, TreatmentBooking>
     */
    public function bookings(Order $order): Collection
    {
        if (! is_module_enabled('TreatmentReservation')) {
            return collect();
        }

        $order->loadMissing([
            'treatmentBookings.beautician',
            'treatmentBookings.product',
            'treatmentBookings.orderProduct.options.values',
            'treatmentBookings.orderProduct.variations.values',
            'spaBranch',
        ]);

        if ($order->relationLoaded('treatmentBookings') && $order->treatmentBookings->isNotEmpty()) {
            return $order->treatmentBookings->values();
        }

        if ($order->treatmentBooking) {
            return collect([$order->treatmentBooking]);
        }

        return collect();
    }


    public function hasLines(Order $order): bool
    {
        return $this->lines($order) !== [];
    }


    /**
     * @return list<array{type: string, label?: string, value: string, spaced?: bool}>
     */
    public function lines(Order $order): array
    {
        $lines = [];
        $bookings = $this->bookings($order);

        if ($order->spaBranch) {
            $lines[] = [
                'type' => 'row',
                'label' => trans('storefront::checkout.spa_branch'),
                'value' => (string) $order->spaBranch->name,
            ];
        }

        foreach ($bookings as $index => $booking) {
            if (! $booking instanceof TreatmentBooking) {
                continue;
            }

            $meta = $booking->treatmentLineMeta();

            $lines[] = [
                'type' => 'product',
                'value' => ($index + 1).'. '.$meta['product_name'],
                'spaced' => $index > 0,
            ];

            if (filled($meta['treatment_selection'] ?? null)) {
                $lines[] = [
                    'type' => 'row',
                    'label' => trans('order::orders.selected_treatment'),
                    'value' => (string) $meta['treatment_selection'],
                ];
            }

            $lines[] = [
                'type' => 'row',
                'label' => trans('storefront::checkout.beautician'),
                'value' => $booking->beautician?->name
                    ?: trans('storefront::checkout.summary_beautician_pending'),
            ];

            $lines[] = [
                'type' => 'row',
                'label' => trans('storefront::checkout.appointment_schedule'),
                'value' => $this->scheduleLabel($booking),
            ];
        }

        if ($bookings->isEmpty() && $this->hasLegacyAppointment($order)) {
            if ($order->beautician) {
                $lines[] = [
                    'type' => 'row',
                    'label' => trans('storefront::checkout.beautician'),
                    'value' => (string) $order->beautician->name,
                ];
            }

            $legacySchedule = collect([
                $order->appointment_date?->format('D, j M Y'),
                $order->appointment_time ? $order->displayAppointmentTime() : null,
            ])->filter()->implode(' · ');

            if ($legacySchedule !== '') {
                $lines[] = [
                    'type' => 'row',
                    'label' => trans('storefront::checkout.appointment_schedule'),
                    'value' => $legacySchedule,
                ];
            }
        }

        return $lines;
    }


    private function scheduleLabel(TreatmentBooking $booking): string
    {
        if ($booking->isTbaSchedule()) {
            return trans('storefront::checkout.schedule_later_tba');
        }

        $parts = [];

        if ($booking->appointment_date) {
            $parts[] = $booking->appointment_date->format('D, j M Y');
        }

        if ($booking->appointment_time) {
            $parts[] = $booking->displayAppointmentTime();
        }

        return $parts !== []
            ? implode(' · ', $parts)
            : trans('storefront::checkout.summary_date_time_pending');
    }


    private function hasLegacyAppointment(Order $order): bool
    {
        return (bool) ($order->beautician_id || $order->appointment_date || $order->appointment_time);
    }
}
