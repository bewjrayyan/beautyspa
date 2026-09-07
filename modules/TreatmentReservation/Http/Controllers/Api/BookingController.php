<?php

namespace Modules\TreatmentReservation\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Requests\PosBookingIndexRequest;
use Modules\TreatmentReservation\Http\Requests\StorePosBookingRequest;
use Modules\TreatmentReservation\Http\Requests\UpdatePosBookingRequest;
use Modules\TreatmentReservation\Http\Resources\PosBookingResource;
use Modules\TreatmentReservation\Services\ManualBookingSlotsResolver;
use Modules\TreatmentReservation\Services\PosBookingService;
use Modules\TreatmentReservation\Services\TreatmentProductDurationService;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyStampAdminService;

class BookingController
{
    public function index(PosBookingIndexRequest $request, PosBookingService $service)
    {
        $bookings = $service->queryFor($request->user(), $request->validated())
            ->paginate((int) ($request->validated('per_page') ?: 25));

        return PosBookingResource::collection($bookings);
    }

    public function store(StorePosBookingRequest $request, PosBookingService $service)
    {
        try {
            if ($request->filled('items')) {
                return PosBookingResource::collection($service->createMany($request->validated(), $request->user()))
                    ->response()
                    ->setStatusCode(201);
            }

            return (new PosBookingResource($service->create($request->validated(), $request->user())))
                ->response()
                ->setStatusCode(201);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function show(TreatmentBooking $booking)
    {
        Gate::authorize('view', $booking);

        if (! $booking->isManualBooking()) {
            abort(404);
        }

        return new PosBookingResource($booking->load(['customer', 'beautician', 'product', 'category']));
    }

    public function update(UpdatePosBookingRequest $request, TreatmentBooking $booking, PosBookingService $service)
    {
        try {
            return new PosBookingResource($service->update($booking, $request->validated(), $request->user()));
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request, TreatmentBooking $booking, PosBookingService $service)
    {
        Gate::authorize('delete', $booking);

        try {
            return new PosBookingResource($service->cancel($booking, $request->user()));
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function availability(
        Request $request,
        ManualBookingSlotsResolver $resolver,
        AppointmentAvailabilityService $appointmentAvailability,
    )
    {
        Gate::authorize('create', TreatmentBooking::class);

        $data = $request->validate([
            'beautician_id' => ['required', 'integer'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'product_id' => ['nullable', 'integer'],
            'spa_branch_id' => ['nullable', 'integer'],
            'booking_id' => ['nullable', 'integer'],
            'holds' => ['nullable', 'array', 'max:20'],
            'holds.*.beautician_id' => ['required_with:holds', 'integer'],
            'holds.*.appointment_date' => ['required_with:holds', 'date'],
            'holds.*.appointment_time' => ['required_with:holds', 'date_format:H:i'],
            'holds.*.product_id' => ['nullable', 'integer'],
            'holds.*.spa_branch_id' => ['nullable', 'integer'],
            'holds.*.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
        ]);

        if ($request->user()->isBeauticianOnly()) {
            $profile = $request->user()->beauticianProfile;
            abort_unless($profile && (int) $profile->id === (int) $data['beautician_id'], 403);
        }

        try {
            $slots = $resolver->resolve([
                'beautician_id' => (int) $data['beautician_id'],
                'date' => $data['date'],
                'product_id' => $data['product_id'] ?? null,
                'spa_branch_id' => $data['spa_branch_id'] ?? null,
                'booking_id' => $data['booking_id'] ?? null,
            ]);
            $duration = ($data['product_id'] ?? null) && ($data['spa_branch_id'] ?? null)
                ? $appointmentAvailability->resolveDurationMinutes((int) $data['product_id'], (int) $data['spa_branch_id'])
                : 60;

            $holds = array_map(function (array $hold) use ($appointmentAvailability, $data) {
                $holdProductId = (int) ($hold['product_id'] ?? 0);
                $holdBranchId = (int) ($hold['spa_branch_id'] ?? $data['spa_branch_id'] ?? 0);

                if ($holdProductId && $holdBranchId) {
                    $hold['duration_minutes'] = $appointmentAvailability->resolveDurationMinutes($holdProductId, $holdBranchId);
                }

                return $hold;
            }, $data['holds'] ?? []);
            // Every line in this POS cart belongs to one customer. Treat all sibling
            // lines as occupied customer time, even when a different beautician is chosen.
            $customerHolds = array_map(function (array $hold) use ($data) {
                $hold['beautician_id'] = (int) $data['beautician_id'];

                return $hold;
            }, $holds);

            $slots = array_values(array_filter($slots, fn ($slot) => ! CheckoutTreatmentScheduleHolds::slotConflictsWithHolds(
                (int) $data['beautician_id'],
                $data['date'],
                (string) $slot,
                $duration,
                $customerHolds,
            )));

            return response()->json([
                'date' => $data['date'],
                'beautician_id' => (int) $data['beautician_id'],
                'duration_minutes' => $duration,
                'slots' => $slots,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function availableDates(Request $request, AppointmentAvailabilityService $availability)
    {
        Gate::authorize('create', TreatmentBooking::class);

        $data = $request->validate([
            'spa_branch_id' => ['required', 'integer', Rule::exists('spa_branches', 'id')->where('is_active', true)],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)->where('is_active', true)->whereNull('deleted_at')],
            'beautician_id' => ['nullable', 'integer', Rule::exists('beauticians', 'id')->where('is_active', true)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $branchId = (int) $data['spa_branch_id'];
        $productId = (int) $data['product_id'];
        $beauticianId = isset($data['beautician_id']) ? (int) $data['beautician_id'] : null;
        $limit = isset($data['limit']) ? (int) $data['limit'] : null;

        if ($beauticianId && ! Beautician::query()->whereKey($beauticianId)
            ->whereHas('spaBranches', fn ($query) => $query->where('spa_branches.id', $branchId))->exists()) {
            throw ValidationException::withMessages(['beautician_id' => 'The selected beautician is not assigned to this spa branch.']);
        }

        if ($request->user()->isBeauticianOnly()) {
            $profile = $request->user()->beauticianProfile;
            abort_unless($profile && (! $beauticianId || (int) $profile->id === $beauticianId), 403);
            abort_unless($profile->spaBranches()->whereKey($branchId)->exists(), 403);
        }

        $from = Carbon::parse($data['from'] ?? today()->toDateString())->max(today())->toDateString();
        $to = Carbon::parse($data['to'] ?? Carbon::parse($from)->addDays(60))->toDateString();

        if (Carbon::parse($from)->diffInDays(Carbon::parse($to)) > 90) {
            throw ValidationException::withMessages(['to' => 'The availability range may not exceed 90 days.']);
        }

        if (! $beauticianId) {
            $beauticianQuery = Beautician::query()
                ->where('is_active', true)
                ->whereHas('spaBranches', fn ($query) => $query->where('spa_branches.id', $branchId));

            if ($request->user()->isBeauticianOnly()) {
                $beauticianQuery->where('user_id', $request->user()->id);
            }

            $dates = $availability->availableDatesAcrossBeauticians(
                $productId,
                $branchId,
                $from,
                $to,
                $beauticianQuery->pluck('id'),
                $limit,
            );

            return response()->json(['dates' => $dates, 'date_options' => []]);
        }

        $options = $availability->dateOptions($productId, $branchId, $from, $to, $beauticianId);
        $available = collect($options)->where('status', 'available')->pluck('date')->values();
        if ($limit !== null) {
            $available = $available->take($limit)->values();
        }

        return response()->json([
            'dates' => $available,
            'date_options' => $options,
        ]);
    }

    public function treatments(Request $request, TreatmentProductDurationService $durations)
    {
        Gate::authorize('create', TreatmentBooking::class);

        $products = Product::query()
            ->where('is_virtual', true)
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('treatment_category_id', $request->integer('category_id')))
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $products->map(function (Product $product) use ($durations) {
            [$minutes] = $durations->resolveMinutesForProduct($product);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->selling_price->amount(),
                'currency' => currency(),
                'duration_minutes' => $minutes,
                'category_id' => $product->treatment_category_id,
            ];
        })->values()]);
    }

    public function customers(Request $request, PosBookingService $service)
    {
        Gate::authorize('create', TreatmentBooking::class);

        $query = trim((string) $request->input('q', ''));
        abort_if(mb_strlen($query) > 50, 422, 'Search is too long.');
        $membershipId = (int) ltrim((string) preg_replace('/[^0-9]/', '', $query), '0');

        $customers = $service->scopeCustomers(\Modules\User\Entities\User::query(), $request->user())
            ->with('loyaltyWallet:id,user_id')
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->whereKey(setting('customer_role')))
            ->when($query !== '', fn ($builder) => $builder->where(function ($where) use ($query, $membershipId) {
                $where->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");

                if ($membershipId > 0) {
                    $where->orWhereHas('loyaltyWallet', fn ($walletQuery) => $walletQuery->whereKey($membershipId));
                }
            }))
            ->latest('id')
            ->limit(25)
            ->get()
            ->filter(fn ($customer) => $customer->isActivated())
            ->values()
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => trim($customer->first_name . ' ' . $customer->last_name),
                'phone' => $customer->phone,
                'email' => $customer->email,
                'membership_id' => $customer->loyaltyWallet?->id
                    ? str_pad((string) $customer->loyaltyWallet->id, 16, '0', STR_PAD_LEFT)
                    : null,
            ]);

        return response()->json(['data' => $customers]);
    }

    public function membershipLookup(
        Request $request,
        LoyaltyStampAdminService $stamps,
        LoyaltyConfig $config,
        PosBookingService $service,
    )
    {
        Gate::authorize('create', TreatmentBooking::class);

        $raw = preg_replace('/[^0-9]/', '', (string) $request->input('membership_id', ''));
        abort_if($raw === '' || strlen($raw) > 16, 422, 'Enter a valid membership ID.');

        $walletId = (int) ltrim($raw, '0');
        abort_if($walletId < 1, 404, 'Membership not found.');

        // Membership cards use the loyalty wallet ID, not the customer user ID.
        $wallet = LoyaltyWallet::query()
            ->with(['user', 'tier'])
            ->whereKey($walletId)
            ->first();
        $customer = $wallet?->user;

        abort_unless($customer && $customer->roles()->whereKey(setting('customer_role'))->exists(), 404, 'Membership not found.');

        abort_unless($customer && $customer->isActivated(), 404, 'Membership not found.');
        try {
            $service->assertCustomerAccessible($customer, $request->user());
        } catch (\InvalidArgumentException) {
            abort(404, 'Membership not found.');
        }


        $stampData = $stamps->memberStampData($customer);

        return response()->json([
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => trim($customer->first_name . ' ' . $customer->last_name),
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ],
                'membership_id' => str_pad((string) $wallet->id, 16, '0', STR_PAD_LEFT),
                'tier' => $wallet?->tier?->name,
                'points' => (int) ($wallet?->balance ?? 0),
                'points_value_rm' => $config->pointsToRm((int) ($wallet?->balance ?? 0)),
                'point_value_rm' => $config->pointValueRm(),
                'max_redeem_percent' => $config->maxRedeemPercent(),
                'stamp_cards' => [
                    'active' => collect($stampData['active_cards'])->map(fn ($card) => [
                        'earned' => (int) ($card->stamps_count ?? 0),
                        'required' => (int) ($card->program?->stamps_required ?? 0),
                    ])->values(),
                    'ready_to_redeem' => count($stampData['ready_to_redeem']),
                ],
            ],
        ]);
    }

    public function beauticians(Request $request)
    {
        Gate::authorize('create', TreatmentBooking::class);

        $query = Beautician::query()->where('is_active', true)->with('spaBranches:id');
        if ($request->user()->isBeauticianOnly()) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json(['data' => $query->orderBy('position')->get()->map(fn (Beautician $beautician) => [
            'id' => $beautician->id,
            'name' => $beautician->name,
            'job_title' => $beautician->job_title,
            'profile_color' => $beautician->profile_color,
            'spa_branch_ids' => $beautician->spaBranches->pluck('id')->values(),
        ])]);
    }
    public function spaBranches(Request $request)
    {
        Gate::authorize('create', TreatmentBooking::class);

        $query = SpaBranch::query()->where('is_active', true)->orderBy('position')->orderBy('name');

        if ($request->user()->isBeauticianOnly()) {
            $beautician = $request->user()->beauticianProfile;
            $query->whereHas('beauticians', fn ($branchQuery) => $branchQuery->whereKey($beautician?->id));
        }

        return response()->json(['data' => $query->get(['id', 'name', 'code'])]);
    }
}
