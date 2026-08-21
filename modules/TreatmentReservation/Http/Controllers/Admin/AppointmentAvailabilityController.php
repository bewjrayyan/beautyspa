<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Entities\AppointmentDateOverride;
use Modules\TreatmentReservation\Entities\SpaBranchWeeklyAvailability;
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityAdminService;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

class AppointmentAvailabilityController extends Controller
{
    public function __construct(
        private AppointmentAvailabilityAdminService $admin,
        private AppointmentAvailabilityService $availability,
    ) {}


    public function index(Request $request): View
    {
        $branches = SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branchId = (int) ($request->integer('spa_branch_id') ?: ($branches->first()?->id ?? 0));
        $productId = (int) $request->integer('product_id');

        $products = Product::withoutGlobalScope('active')
            ->where('is_virtual', true)
            ->with('translations')
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ])
            ->values();

        $treatment = $productId > 0 && $branchId > 0
            ? TreatmentBranchAvailability::query()
                ->with(['weeklyDays.slots'])
                ->where('product_id', $productId)
                ->where('spa_branch_id', $branchId)
                ->first()
            : null;

        $overrides = AppointmentDateOverride::query()
            ->with(['slots', 'product'])
            ->when($branchId > 0, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->where(function ($query) use ($productId) {
                if ($productId > 0) {
                    $query->where('product_id', $productId)
                        ->orWhere('product_id', AppointmentDateOverride::PRODUCT_ALL);

                    return;
                }

                $query->where('product_id', AppointmentDateOverride::PRODUCT_ALL);
            })
            ->where('override_date', '>=', today()->subDays(7)->toDateString())
            ->orderBy('override_date')
            ->limit(100)
            ->get();

        $branchDays = $this->branchDaysPayload($branchId);
        // When treatment settings do not exist yet, seed the editor from branch hours
        // so Save does not accidentally persist an all-closed treatment schedule.
        $treatmentDays = $treatment
            ? $this->treatmentDaysPayload($treatment)
            : ($productId > 0 ? $branchDays : $this->treatmentDaysPayload(null));

        return view('treatmentreservation::admin.appointment_availability.index', [
            'branches' => $branches,
            'branchId' => $branchId,
            'productId' => $productId,
            'products' => $products,
            'branchDays' => $branchDays,
            'treatment' => $treatment,
            'treatmentDays' => $treatmentDays,
            'overrides' => $overrides,
            'weekdayLabels' => $this->weekdayLabels(),
        ]);
    }


    public function syncBranch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'spa_branch_id' => ['required', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'days' => ['required', 'array', 'size:7'],
            'days.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'days.*.is_open' => ['required', 'boolean'],
            'days.*.times' => ['nullable', 'array', 'max:48'],
            'days.*.times.*' => ['required', 'date_format:H:i', 'distinct'],
        ]);

        if ($error = $this->validateOpenDaysHaveTimes($data['days'])) {
            return response()->json(['message' => $error], 422);
        }

        $this->admin->syncBranchWeekly((int) $data['spa_branch_id'], $data['days']);

        return response()->json([
            'message' => TrLang::trans('admin.appointment_availability.branch_saved'),
            'days' => $this->branchDaysPayload((int) $data['spa_branch_id']),
        ]);
    }


    public function syncTreatment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query->where('is_virtual', true)->where('is_active', true))],
            'spa_branch_id' => ['required', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:600'],
            'capacity_per_slot' => ['required', 'integer', 'min:1', 'max:100'],
            'allow_tba' => ['required', 'boolean'],
            'is_bookable' => ['required', 'boolean'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'days.*.is_open' => ['required', 'boolean'],
            'days.*.times' => ['nullable', 'array', 'max:48'],
            'days.*.times.*' => ['required', 'date_format:H:i', 'distinct'],
        ]);

        if ($error = $this->validateOpenDaysHaveTimes($data['days'])) {
            return response()->json(['message' => $error], 422);
        }

        $settings = $this->admin->syncTreatmentBranch(
            (int) $data['product_id'],
            (int) $data['spa_branch_id'],
            $data
        );

        return response()->json([
            'message' => TrLang::trans('admin.appointment_availability.treatment_saved'),
            'settings' => [
                'duration_minutes' => $settings->duration_minutes,
                'capacity_per_slot' => $settings->capacity_per_slot,
                'allow_tba' => $settings->allow_tba,
                'is_bookable' => $settings->is_bookable,
            ],
            'days' => $this->treatmentDaysPayload($settings),
        ]);
    }


    public function storeOverride(Request $request): JsonResponse
    {
        $data = $request->validate([
            'spa_branch_id' => ['required', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'product_id' => ['nullable', 'integer'],
            'override_date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:open,closed,custom'],
            'reason' => ['nullable', 'string', 'max:255'],
            'capacity_per_slot' => ['nullable', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:600'],
            'times' => ['nullable', 'array', 'max:48'],
            'times.*' => ['required', 'date_format:H:i', 'distinct'],
        ]);

        $data['product_id'] = (int) ($data['product_id'] ?? AppointmentDateOverride::PRODUCT_ALL);

        if ($data['product_id'] > 0 && ! Product::withoutGlobalScope('active')
            ->whereKey($data['product_id'])
            ->where('is_virtual', true)
            ->where('is_active', true)
            ->exists()) {
            return response()->json([
                'message' => TrLang::trans('admin.appointment_availability.invalid_product'),
            ], 422);
        }

        $times = array_values(array_filter(array_map('strval', $data['times'] ?? [])));
        if ($data['status'] === 'custom' && $times === []) {
            return response()->json([
                'message' => TrLang::trans('admin.appointment_availability.custom_times_required'),
            ], 422);
        }

        if ($data['status'] === 'open' && $times === []) {
            // OPEN without times inherits weekly hours; reject if that weekday is closed.
            $dow = Carbon::parse($data['override_date'])->dayOfWeek;
            $hasWeekly = false;
            if ($data['product_id'] > 0) {
                $settings = TreatmentBranchAvailability::query()
                    ->with(['weeklyDays.slots'])
                    ->where('product_id', $data['product_id'])
                    ->where('spa_branch_id', (int) $data['spa_branch_id'])
                    ->first();
                $day = $settings?->weeklyDays->firstWhere('day_of_week', $dow);
                $hasWeekly = $day && $day->is_open && $day->slots->where('is_enabled', true)->isNotEmpty();
            }
            if (! $hasWeekly) {
                $branchDay = SpaBranchWeeklyAvailability::query()
                    ->with('slots')
                    ->where('spa_branch_id', (int) $data['spa_branch_id'])
                    ->where('day_of_week', $dow)
                    ->first();
                $hasWeekly = $branchDay
                    && $branchDay->is_open
                    && $branchDay->slots->where('is_enabled', true)->isNotEmpty();
            }
            if (! $hasWeekly) {
                return response()->json([
                    'message' => TrLang::trans('admin.appointment_availability.open_needs_weekly_or_times'),
                ], 422);
            }
        }

        $override = $this->admin->upsertDateOverride($data);

        return response()->json([
            'message' => TrLang::trans('admin.appointment_availability.override_saved'),
            'override' => $this->overridePayload($override),
        ]);
    }


    public function destroyOverride(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'spa_branch_id' => ['required', 'integer', 'exists:spa_branches,id'],
        ]);

        $override = AppointmentDateOverride::query()->find($id);

        if (! $override || (int) $override->spa_branch_id !== (int) $data['spa_branch_id']) {
            return response()->json([
                'message' => TrLang::trans('admin.appointment_availability.override_not_found'),
            ], 404);
        }

        $this->admin->deleteDateOverride($override->id);

        return response()->json([
            'message' => TrLang::trans('admin.appointment_availability.override_deleted'),
        ]);
    }


    public function previewSlots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query->where('is_virtual', true)->where('is_active', true))],
            'spa_branch_id' => ['required', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'beautician_id' => ['nullable', 'integer'],
        ]);

        $slots = $this->availability->availableSlots(
            (int) $data['product_id'],
            (int) $data['spa_branch_id'],
            $data['date'],
            isset($data['beautician_id']) ? (int) $data['beautician_id'] : null,
        );

        $day = $this->availability->resolveDaySchedule(
            (int) $data['product_id'],
            (int) $data['spa_branch_id'],
            $data['date'],
        );

        return response()->json([
            'slots' => $slots,
            'day' => $day,
            'date' => $data['date'],
        ]);
    }


    /**
     * @return list<array{day_of_week: int, label: string, is_open: bool, times: list<string>}>
     */
    private function branchDaysPayload(int $branchId): array
    {
        $rows = SpaBranchWeeklyAvailability::query()
            ->with('slots')
            ->where('spa_branch_id', $branchId)
            ->get()
            ->keyBy('day_of_week');

        $out = [];

        foreach ($this->weekdayLabels() as $dow => $label) {
            $row = $rows->get($dow);
            $times = [];

            if ($row) {
                foreach ($row->slots as $slot) {
                    if ($slot->is_enabled) {
                        $times[] = Carbon::parse($slot->start_time)->format('H:i');
                    }
                }
            }

            $out[] = [
                'day_of_week' => $dow,
                'label' => $label,
                'is_open' => (bool) ($row?->is_open),
                'times' => $times,
            ];
        }

        return $out;
    }


    /**
     * @return list<array{day_of_week: int, label: string, is_open: bool, times: list<string>}>
     */
    private function treatmentDaysPayload(?TreatmentBranchAvailability $settings): array
    {
        $rows = $settings
            ? $settings->weeklyDays->keyBy('day_of_week')
            : collect();

        $out = [];

        foreach ($this->weekdayLabels() as $dow => $label) {
            $row = $rows->get($dow);
            $times = [];

            if ($row) {
                foreach ($row->slots as $slot) {
                    if ($slot->is_enabled) {
                        $times[] = Carbon::parse($slot->start_time)->format('H:i');
                    }
                }
            }

            $out[] = [
                'day_of_week' => $dow,
                'label' => $label,
                'is_open' => (bool) ($row?->is_open),
                'times' => $times,
            ];
        }

        return $out;
    }


    /**
     * @return array<int, string>
     */

    /**
     * @param  list<array{is_open?: bool, times?: list<string>}>  $days
     */
    private function validateOpenDaysHaveTimes(array $days): ?string
    {
        foreach ($days as $day) {
            if (! (bool) ($day['is_open'] ?? false)) {
                continue;
            }

            $times = array_values(array_filter(array_map('strval', $day['times'] ?? [])));
            if ($times === []) {
                return TrLang::trans('admin.appointment_availability.open_day_needs_times');
            }
        }

        return null;
    }


    private function weekdayLabels(): array
    {
        return [
            0 => TrLang::trans('admin.appointment_availability.weekdays.sun'),
            1 => TrLang::trans('admin.appointment_availability.weekdays.mon'),
            2 => TrLang::trans('admin.appointment_availability.weekdays.tue'),
            3 => TrLang::trans('admin.appointment_availability.weekdays.wed'),
            4 => TrLang::trans('admin.appointment_availability.weekdays.thu'),
            5 => TrLang::trans('admin.appointment_availability.weekdays.fri'),
            6 => TrLang::trans('admin.appointment_availability.weekdays.sat'),
        ];
    }


    private function overridePayload(AppointmentDateOverride $override): array
    {
        return [
            'id' => $override->id,
            'spa_branch_id' => $override->spa_branch_id,
            'product_id' => $override->product_id,
            'treatment_name' => $override->isBranchWide() ? null : $override->product?->name,
            'override_date' => $override->override_date?->format('Y-m-d'),
            'status' => $override->status,
            'reason' => $override->reason,
            'capacity_per_slot' => $override->capacity_per_slot,
            'duration_minutes' => $override->duration_minutes,
            'times' => $override->slots
                ->filter(fn ($slot) => $slot->is_enabled)
                ->map(fn ($slot) => Carbon::parse($slot->start_time)->format('H:i'))
                ->values()
                ->all(),
        ];
    }
}
