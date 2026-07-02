<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalPasswordRequest;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalProfileRequest;
use Modules\TreatmentReservation\Services\BeauticianIcalFeedService;
use Modules\TreatmentReservation\Services\PortalProfileUpdateService;

class PortalAccountController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->routeIs('admin.treatment_reservations.portal.account')) {
            $beautician = Beautician::findForUser(auth()->id());

            if ($beautician && auth()->user()?->isBeauticianOnly()) {
                return redirect()->route('admin.beauticians.portal.account', $beautician->id);
            }
        }

        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');
        $ical = app(BeauticianIcalFeedService::class);

        return view('treatmentreservation::admin.portal.account', [
            'beautician' => $beautician,
            'user' => auth()->user(),
            'icalUrl' => $ical->feedUrl($beautician),
            'icalWebcalUrl' => $ical->webcalUrl($beautician),
            'icalGoogleUrl' => $ical->googleCalendarSubscribeUrl($beautician),
            'icalOutlookUrl' => $ical->outlookSubscribeUrl($beautician),
            'accountRoutes' => $this->accountRoutes($beautician),
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


    /**
     * @return array{dashboard: string, availability: string, profileUpdate: string, passwordUpdate: string}
     */
    private function accountRoutes(Beautician $beautician): array
    {
        if (request()->routeIs('admin.beauticians.portal.account*')) {
            $beauticianId = $beautician->id;

            return [
                'dashboard' => route('admin.beauticians.portal.dashboard', $beauticianId),
                'availability' => route('admin.beauticians.portal.availability', $beauticianId),
                'profileUpdate' => route('admin.beauticians.portal.account.profile', $beauticianId),
                'passwordUpdate' => route('admin.beauticians.portal.account.password', $beauticianId),
            ];
        }

        return [
            'dashboard' => route('admin.treatment_reservations.portal'),
            'availability' => route('admin.treatment_reservations.portal.availability'),
            'profileUpdate' => route('admin.treatment_reservations.portal.account.profile'),
            'passwordUpdate' => route('admin.treatment_reservations.portal.account.password'),
        ];
    }
}
