<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalPasswordRequest;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalProfileRequest;
use Modules\TreatmentReservation\Services\BeauticianIcalFeedService;
use Modules\TreatmentReservation\Services\PortalProfileUpdateService;

class PortalAccountController extends Controller
{
    public function edit(): View
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');
        $ical = app(BeauticianIcalFeedService::class);

        return view('treatmentreservation::admin.portal.account', [
            'beautician' => $beautician,
            'user' => auth()->user(),
            'icalUrl' => $ical->feedUrl($beautician),
            'icalWebcalUrl' => $ical->webcalUrl($beautician),
            'icalGoogleUrl' => $ical->googleCalendarSubscribeUrl($beautician),
            'icalOutlookUrl' => $ical->outlookSubscribeUrl($beautician),
        ]);
    }


    public function updatePassword(UpdatePortalPasswordRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'password' => bcrypt($request->input('password')),
        ]);

        return back()->withSuccess(trans('treatmentreservation::admin.portal.password_updated'));
    }


    public function updateProfile(UpdatePortalProfileRequest $request): RedirectResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        app(PortalProfileUpdateService::class)->update(
            auth()->user(),
            $beautician,
            $request->validated()
        );

        return back()->withSuccess(trans('treatmentreservation::admin.portal.profile_updated'));
    }
}
