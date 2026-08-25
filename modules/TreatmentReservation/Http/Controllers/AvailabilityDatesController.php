<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\Cart\Facades\Cart;
use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;

class AvailabilityDatesController extends Controller
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
    ) {}


    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'spa_branch_id' => ['required', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'beautician_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $branchId = (int) $data['spa_branch_id'];
        $cartProductIds = $this->resolveCartTreatmentProductIds();
        $productId = (int) ($data['product_id'] ?? 0) ?: ($cartProductIds[0] ?? 0);

        if (! $productId) {
            return response()->json(['dates' => []]);
        }

        if ($cartProductIds !== [] && ! in_array($productId, $cartProductIds, true)) {
            throw ValidationException::withMessages(['product_id' => trans('validation.in')]);
        }

        $this->validateScope($branchId, $productId, isset($data['beautician_id']) ? (int) $data['beautician_id'] : null);

        // Clamp past "from" to today — browsers using toISOString() can send yesterday in UTC+ timezones.
        $from = $data['from'] ?? today()->toDateString();
        if (Carbon::parse($from)->lt(today())) {
            $from = today()->toDateString();
        }
        $to = $data['to'] ?? Carbon::parse($from)->addDays(60)->toDateString();
        if (Carbon::parse($to)->lt(Carbon::parse($from))) {
            $to = Carbon::parse($from)->addDays(60)->toDateString();
        }

        if (Carbon::parse($from)->diffInDays(Carbon::parse($to)) > 90) {
            throw ValidationException::withMessages(['to' => trans('validation.max.numeric', [
                'attribute' => 'to',
                'max' => 90,
            ])]);
        }

        $beauticianId = isset($data['beautician_id']) ? (int) $data['beautician_id'] : null;

        $dateOptions = $this->availability->dateOptions(
            $productId,
            $branchId,
            $from,
            $to,
            $beauticianId,
        );

        $dates = array_values(array_map(
            static fn (array $option) => $option['date'],
            array_filter($dateOptions, static fn (array $option) => $option['status'] === 'available')
        ));

        return response()->json([
            'dates' => $dates,
            'date_options' => $dateOptions,
        ]);
    }

    private function validateScope(int $branchId, int $productId, ?int $beauticianId): void
    {
        if (! SpaBranch::query()->whereKey($branchId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['spa_branch_id' => trans('validation.exists')]);
        }

        if (! Product::withoutGlobalScope('active')->whereKey($productId)->where('is_virtual', true)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['product_id' => trans('validation.exists')]);
        }

        if ($beauticianId && ! Beautician::query()
            ->whereKey($beauticianId)
            ->where('is_active', true)
            ->whereHas('spaBranches', fn ($query) => $query->where('spa_branches.id', $branchId))
            ->exists()) {
            throw ValidationException::withMessages(['beautician_id' => trans('validation.exists')]);
        }
    }


    /**
     * @return list<int>
     */
    private function resolveCartTreatmentProductIds(): array
    {
        $ids = [];

        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if ($product && ($product->is_virtual ?? false)) {
                $ids[] = (int) $product->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
