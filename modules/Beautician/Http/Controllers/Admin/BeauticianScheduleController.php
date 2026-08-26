<?php

namespace Modules\Beautician\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Events\OrderUpdated;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class BeauticianScheduleController
{
    public function calendarEvents(Request $request, int $id): JsonResponse
    {
        $this->findBeautician($id);

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $bookings = TreatmentBooking::query()
            ->forCalendar($request->input('month'), $id)
            ->get()
            ->map->toCalendarPayload();

        return response()->json(['bookings' => $bookings]);
    }


    public function kanbanBoard(int $id): JsonResponse
    {
        $this->findBeautician($id);

        $bookings = TreatmentBooking::query()
            ->forKanban($id)
            ->get();

        $columns = [];

        foreach (TreatmentBooking::kanbanStatuses() as $status) {
            $columns[$status] = $bookings
                ->where('status', $status)
                ->values()
                ->map->toKanbanPayload();
        }

        return response()->json(['columns' => $columns]);
    }


    public function updateStatus(Request $request, int $id, int $bookingId): JsonResponse
    {
        $this->findBeautician($id);

        $request->validate([
            'status' => ['required', 'in:' . implode(',', TreatmentBooking::kanbanStatuses())],
        ]);

        $booking = TreatmentBooking::query()
            ->where('beautician_id', $id)
            ->findOrFail($bookingId);

        $previousBookingStatus = $booking->status;
        $newBookingStatus = (string) $request->input('status');

        if ($previousBookingStatus === $newBookingStatus) {
            return response()->json([
                'booking' => $booking->fresh()->toKanbanPayload(),
            ]);
        }

        $booking->update(['status' => $newBookingStatus]);

        if ($booking->order_id) {
            $order = $booking->order;
            $updates = [];

            if ($newBookingStatus === TreatmentBooking::STATUS_COMPLETED) {
                $updates['status'] = Order::COMPLETED;
            } elseif ($newBookingStatus === TreatmentBooking::STATUS_IN_PROGRESS) {
                $updates['status'] = Order::PROCESSING;
            }

            $orderStatusChanged = false;

            if ($updates !== []) {
                $previousOrderStatus = $order->status;
                $order->update($updates);

                if ($previousOrderStatus !== $order->status) {
                    event(new OrderStatusChanged(
                        $order->fresh(['treatmentBookings', 'beautician']),
                        'order',
                        $previousOrderStatus,
                        $order->status
                    ));
                    $orderStatusChanged = true;
                }
            }

            // Treatment-only updates (order status unchanged) still notify via WhatsApp.
            if (! $orderStatusChanged) {
                $freshOrder = $order->fresh(['treatmentBookings', 'beautician']);
                event(new OrderUpdated($freshOrder));
                event(new OrderStatusChanged(
                    $freshOrder,
                    'treatment',
                    $previousBookingStatus,
                    $newBookingStatus
                ));
            }
        }

        return response()->json([
            'booking' => $booking->fresh()->toKanbanPayload(),
        ]);
    }


    private function findBeautician(int $id): Beautician
    {
        return Beautician::findOrFail($id);
    }
}
