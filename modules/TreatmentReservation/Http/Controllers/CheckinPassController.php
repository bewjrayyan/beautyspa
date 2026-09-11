<?php

declare(strict_types=1);

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

final class CheckinPassController extends Controller
{
    public function show(Request $request, TreatmentBooking $booking): View
    {
        $booking->loadMissing(['product', 'beautician']);
        $user = auth()->user();

        return view('treatmentreservation::public.booking.checkin-pass', [
            'booking' => $booking,
            'canConfirm' => $user?->hasAccess('admin.leads.edit') ?? false,
            'checkinUrl' => aestheticcart_apply_install_base_url($request->fullUrl()),
        ]);
    }
}
