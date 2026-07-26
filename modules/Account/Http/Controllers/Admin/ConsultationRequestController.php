<?php

namespace Modules\Account\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationFormTemplate;
use Modules\Account\Exceptions\ConsultationRequestException;
use Modules\Account\Services\ConsultationRequestService;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class ConsultationRequestController extends Controller
{
    public function store(
        Request $request,
        int $id,
        ConsultationRequestService $consultations
    ): JsonResponse {
        $request->validate([
            'template_id' => ['nullable', 'integer', 'exists:consultation_form_templates,id'],
        ]);

        $bookingId = (int) ($request->route('booking') ?: $id);
        $booking = TreatmentBooking::query()
            ->with(['order.customer', 'beautician', 'product'])
            ->findOrFail($bookingId);
        $portalBeautician = $request->attributes->get('portal_beautician');

        if ($portalBeautician instanceof Beautician) {
            abort_unless((int) $booking->beautician_id === (int) $portalBeautician->id, 403);
        }

        $template = $request->filled('template_id')
            ? ConsultationFormTemplate::query()->where('is_active', true)->findOrFail($request->integer('template_id'))
            : null;

        try {
            $consultation = $consultations->createForBooking($booking, $request->user(), $template);
        } catch (ConsultationRequestException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => trans('account::consultation.request.ready'),
            'consultation_id' => $consultation->id,
            'share_url' => $consultations->shareUrl($consultation),
            'whatsapp_url' => $consultations->whatsAppUrl($consultation),
            'customer_has_account' => $consultation->user_id !== null,
        ]);
    }
}
