<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\BeauticianIcalFeedService;

class CalendarFeedController extends Controller
{
    public function __invoke(int $beautician, string $token, BeauticianIcalFeedService $feed): Response
    {
        if (! $feed->isValidToken($beautician, $token)) {
            abort(404);
        }

        $beauticianModel = Beautician::query()
            ->where('id', $beautician)
            ->where('is_active', true)
            ->firstOrFail();

        return response($feed->generate($beauticianModel), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="beautician-' . $beautician . '.ics"',
        ]);
    }
}
