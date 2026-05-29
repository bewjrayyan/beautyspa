<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\TreatmentReservation\Services\BookingJobSheetOrderSync;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;
use Modules\TreatmentReservation\Services\BookingCustomerWhatsAppService;

class PortalController extends Controller
{
    public function __construct(
        private ReservationDashboardService $dashboard
    ) {}


    public function jobSheet(Request $request)
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $todayAppointments = $this->dashboard->todayAppointmentsForBeautician($beautician->id);

        $activeView = in_array($request->query('view'), ['kanban', 'calendar'], true)
            ? $request->query('view')
            : 'kanban';

        $calendarFocus = $request->boolean('focus') && $activeView === 'calendar';

        return view('treatmentreservation::admin.portal.job_sheet', [
            'beautician' => $beautician,
            'stats' => $this->dashboard->statsForBeauticianSchedule($beautician->id),
            'performanceStats' => $this->dashboard->statsForBeautician($beautician->id),
            'todayAppointments' => $todayAppointments,
            'todayBookingsPayload' => $todayAppointments->map->toKanbanPayload()->values(),
            'activeView' => $activeView,
            'calendarFocus' => $calendarFocus,
        ]);
    }


    public function calendarEvents(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $bookings = TreatmentBooking::query()
            ->forCalendar($request->input('month'), $beautician->id)
            ->get()
            ->map->toCalendarPayload();

        return response()->json(['bookings' => $bookings]);
    }


    public function kanbanBoard(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $bookings = TreatmentBooking::query()
            ->forKanban($beautician->id, $request->integer('treatment_category_id') ?: null)
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


    public function updateStatus(Request $request, int $id): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'status' => ['required', 'in:' . implode(',', TreatmentBooking::kanbanStatuses())],
        ]);

        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($id);

        $previousStatus = $booking->status;
        $booking->update(['status' => $request->input('status')]);

        app(TreatmentBookingActivityLogger::class)->logStatusChange(
            $booking,
            $previousStatus,
            $request->input('status')
        );

        app(BookingJobSheetOrderSync::class)->syncOrderStatus(
            $booking,
            $request->input('status')
        );

        $freshBooking = $booking->fresh();

        return response()->json([
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function updateBeauticianNotes(Request $request, int $id): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'beautician_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = TreatmentBooking::query()
            ->where('beautician_id', $beautician->id)
            ->findOrFail($id);

        $previousNotes = $booking->beautician_notes;
        $booking->update([
            'beautician_notes' => $request->input('beautician_notes'),
        ]);

        app(TreatmentBookingActivityLogger::class)->logBeauticianNotes(
            $booking,
            $previousNotes,
            $request->input('beautician_notes')
        );

        $freshBooking = $booking->fresh();

        return response()->json([
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function sendCustomerWhatsApp(Request $request, int $id, BookingCustomerWhatsAppService $whatsapp): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = TreatmentBooking::query()
            ->with(['beautician', 'product'])
            ->where('beautician_id', $beautician->id)
            ->findOrFail($id);

        try {
            $whatsapp->send($booking, $request->input('message'));
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: trans('treatmentreservation::admin.calendar.whatsapp_failed'),
            ], 422);
        }

        app(TreatmentBookingActivityLogger::class)->logWhatsAppSent($booking);

        $freshBooking = $booking->fresh();

        return response()->json([
            'message' => trans('treatmentreservation::admin.calendar.whatsapp_sent'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }
}
