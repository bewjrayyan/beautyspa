<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;

class PortalAvailabilityController extends Controller
{
    public function __construct(
        private BeauticianAvailabilityService $availability
    ) {}


    public function edit(): View
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');

        return view('treatmentreservation::admin.portal.availability', [
            'beautician' => $beautician,
            'user' => auth()->user(),
            'workingHours' => $this->availability->workingHoursFor($beautician->id),
            'blockedTimes' => $this->availability->upcomingBlocksFor($beautician->id),
            'days' => $this->dayLabels(),
        ]);
    }


    public function updateHours(Request $request): RedirectResponse
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');

        $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.start_time' => ['nullable', 'string'],
            'hours.*.end_time' => ['nullable', 'string'],
            'hours.*.enabled' => ['nullable', 'boolean'],
        ]);

        $this->availability->syncWorkingHours($beautician->id, $request->input('hours', []));

        return back()->withSuccess(trans('treatmentreservation::admin.availability.hours_saved'));
    }


    public function storeBlock(Request $request): RedirectResponse
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');

        $request->validate([
            'block_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->availability->addBlockedTime(
                $beautician->id,
                $request->input('block_date'),
                $request->input('start_time'),
                $request->input('end_time'),
                $request->input('note')
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withError($exception->getMessage());
        }

        return back()->withSuccess(trans('treatmentreservation::admin.availability.block_added'));
    }


    public function destroyBlock(int $blockId): RedirectResponse
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');

        $this->availability->removeBlockedTime($beautician->id, $blockId);

        return back()->withSuccess(trans('treatmentreservation::admin.availability.block_removed'));
    }


    public function slots(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = request()->attributes->get('portal_beautician');

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return response()->json([
            'slots' => $this->availability->availableSlots(
                $beautician->id,
                $request->input('date')
            ),
        ]);
    }


    /**
     * @return array<int, string>
     */
    private function dayLabels(): array
    {
        return [
            0 => trans('treatmentreservation::admin.availability.days.sun'),
            1 => trans('treatmentreservation::admin.availability.days.mon'),
            2 => trans('treatmentreservation::admin.availability.days.tue'),
            3 => trans('treatmentreservation::admin.availability.days.wed'),
            4 => trans('treatmentreservation::admin.availability.days.thu'),
            5 => trans('treatmentreservation::admin.availability.days.fri'),
            6 => trans('treatmentreservation::admin.availability.days.sat'),
        ];
    }
}
