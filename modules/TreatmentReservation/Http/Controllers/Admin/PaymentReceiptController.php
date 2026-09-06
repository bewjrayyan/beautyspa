<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class PaymentReceiptController extends Controller
{
    public function show(TreatmentBooking $booking)
    {
        Gate::authorize("view", $booking);
        abort_unless($booking->isManualBooking() && $booking->paymentReceipt, 404);

        $file = $booking->paymentReceipt;
        $path = (string) $file->getRawOriginal("path");
        $disk = Storage::disk((string) $file->disk);
        abort_unless($path !== "" && $disk->exists($path), 404);

        return $disk->response($path, basename((string) $file->filename), [
            "Content-Type" => $file->mime ?: "application/octet-stream",
            "Content-Disposition" => "inline",
            "X-Content-Type-Options" => "nosniff",
            "Cache-Control" => "private, no-store, max-age=0",
        ]);
    }
}
