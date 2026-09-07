<?php

namespace Modules\Order\Http\Requests;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Illuminate\Validation\Rule;
use Modules\Payment\Facades\Gateway;
use Modules\Payment\Services\ChipPaymentMethodConfig;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\Checkout\Exceptions\CheckoutException;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;
use Modules\User\Support\PhoneNumber;

class StoreOrderRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'checkout::attributes';


    /**
     * Validate the class instance.
     *
     * @return void
     * @throws Exception
     */
    public function prepareForValidation()
    {
        $treatmentItems = Cart::items()->filter(
            fn ($item) => (bool) ($item->product?->isVirtualTreatment())
        );

        if ($treatmentItems->contains(fn ($item) => (int) $item->qty !== 1)) {
            throw new CheckoutException(trans('checkout::messages.single_treatment_qty'));
        }

        if (! Cart::allItemsAreVirtual() && ! $this->input('shipping_method')) {
            throw new CheckoutException(trans('checkout::messages.no_shipping_method'));
        }

        if ($this->filled('appointment_time')) {
            $this->merge([
                'appointment_time' => substr((string) $this->input('appointment_time'), 0, 5),
            ]);
        }

        $billing = $this->input('billing', []);

        if (
            ! empty($billing['country'])
            && ! in_array($billing['country'], Country::supportedCodes(), true)
        ) {
            $billing['country'] = Country::supportedCodes()[0] ?? 'MY';
            $billing['state'] = '';

            $this->merge(['billing' => $billing]);
        }

        if ($this->has('customer_phone')) {
            $e164 = PhoneNumber::toE164($this->input('customer_phone'));

            if ($e164 !== '') {
                $this->merge(['customer_phone' => $e164]);
            }
        }

        if ($this->filled('payment_method')) {
            $this->merge([
                'payment_method' => ChipPaymentMethodConfig::normalizeGatewayKey(
                    (string) $this->input('payment_method')
                ),
            ]);
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(
            [
                'customer_email' => ['required', 'email', $this->emailUniqueRule()],
                'customer_phone' => ['required', new ValidPhone()],
                'create_an_account' => 'boolean',
                'password' => 'required_if:create_an_account,1',
                'ship_to_a_different_address' => 'boolean',
                'save_billing_address' => 'boolean',
                'make_billing_address_default' => [
                    'boolean',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if ($this->boolean($attribute) && ! $this->boolean('save_billing_address')) {
                            $fail(trans('validation.prohibited', [
                                'attribute' => str_replace('_', ' ', $attribute),
                            ]));
                        }
                    },
                ],
                'save_shipping_address' => 'boolean',
                'make_shipping_address_default' => [
                    'boolean',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (
                            $this->boolean($attribute)
                            && (
                                ! $this->boolean('save_shipping_address')
                                || $this->boolean('make_billing_address_default')
                            )
                        ) {
                            $fail(trans('validation.prohibited', [
                                'attribute' => str_replace('_', ' ', $attribute),
                            ]));
                        }
                    },
                ],
                'payment_method' => ['required', Rule::in(Gateway::names())],
                'payment_proof' => [
                    Rule::requiredIf(fn () => $this->input('payment_method') === 'bank_transfer'),
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,pdf,webp',
                    'max:10240',
                ],
                'terms_and_conditions' => 'accepted',
                'shipping_method' => Cart::allItemsAreVirtual() ? 'nullable' : 'required',
            ],
            $this->billingAddressRules(),
            $this->shippingAddressRules(),
            $this->spaBranchRules(),
            $this->treatmentBookingRules()
        );
    }


    private function treatmentBookingRules(): array
    {
        if (! Cart::hasVirtualTreatment()) {
            return [];
        }

        $lines = $this->input('treatment_bookings');

        // Legacy single-field payload → normalize into treatment_bookings[]
        if (! is_array($lines) || $lines === []) {
            $productId = $this->resolveCartTreatmentProductId();
            $lines = [[
                'product_id' => $productId,
                'beautician_id' => $this->input('beautician_id'),
                'schedule_later' => $this->boolean('schedule_later') ? 1 : 0,
                'appointment_date' => $this->input('appointment_date'),
                'appointment_time' => $this->input('appointment_time'),
            ]];
            $this->merge(['treatment_bookings' => $lines]);
        }

        $beauticianRules = [
            'required',
            Rule::exists('beauticians', 'id')->where('is_active', true),
            function ($attribute, $value, $fail) {
                if (! app('modules')->isEnabled('SpaBranch') || ! $this->filled('spa_branch_id')) {
                    return;
                }

                $beautician = Beautician::with('spaBranches')->find($value);

                if (! $beautician) {
                    return;
                }

                $branchIds = $beautician->spaBranches->pluck('id');

                if ($branchIds->isEmpty()) {
                    $fail(trans('checkout::messages.beautician_not_assigned_to_branch'));

                    return;
                }

                if (! $branchIds->contains((int) $this->input('spa_branch_id'))) {
                    $fail(trans('checkout::messages.beautician_not_at_branch'));
                }
            },
        ];

        return [
            'treatment_bookings' => ['required', 'array', 'min:1', function (string $attribute, mixed $value, \Closure $fail): void {
                $virtualCount = Cart::items()->filter(fn ($item) => (bool) ($item->product?->isVirtualTreatment()))->count();
                if (! is_array($value) || count($value) !== $virtualCount) {
                    $fail(trans('checkout::messages.treatment_bookings_count_mismatch'));
                }

                $this->assertNoOverlappingSchedules(is_array($value) ? $value : [], $fail);
            }],
            'treatment_bookings.*.cart_item_id' => ['nullable', 'string'],
            'treatment_bookings.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_virtual', true)],
            'treatment_bookings.*.beautician_id' => $beauticianRules,
            'treatment_bookings.*.schedule_later' => [
                'sometimes',
                'boolean',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        return;
                    }

                    if (! preg_match('/treatment_bookings\.(\d+)\./', $attribute, $m)) {
                        return;
                    }

                    $productId = (int) $this->input("treatment_bookings.{$m[1]}.product_id");
                    $branchId = (int) $this->input('spa_branch_id');

                    if (! $productId || ! $branchId) {
                        return;
                    }

                    $settings = TreatmentBranchAvailability::query()
                        ->where('product_id', $productId)
                        ->where('spa_branch_id', $branchId)
                        ->first(['allow_tba', 'is_bookable']);

                    if ($settings && (! $settings->is_bookable || ! $settings->allow_tba)) {
                        $fail(trans('checkout::messages.tba_not_allowed'));
                    }
                },
            ],
            'treatment_bookings.*.appointment_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('/treatment_bookings\.(\d+)\./', $attribute, $m)) {
                        return;
                    }
                    $later = filter_var($this->input("treatment_bookings.{$m[1]}.schedule_later"), FILTER_VALIDATE_BOOLEAN);
                    if (! $later && ! $value) {
                        $fail(trans('validation.required', ['attribute' => 'appointment date']));
                    }
                },
            ],
            'treatment_bookings.*.appointment_time' => [
                'nullable',
                'date_format:H:i',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('/treatment_bookings\.(\d+)\./', $attribute, $m)) {
                        return;
                    }
                    $later = filter_var($this->input("treatment_bookings.{$m[1]}.schedule_later"), FILTER_VALIDATE_BOOLEAN);
                    if (! $later && ! $value) {
                        $fail(trans('validation.required', ['attribute' => 'appointment time']));
                    }
                },
                ...(app('modules')->isEnabled('TreatmentReservation')
                    ? [new \Modules\TreatmentReservation\Rules\ValidBeauticianSlot()]
                    : []),
            ],
            // Legacy top-level fields optional when treatment_bookings present
            'schedule_later' => ['sometimes', 'boolean'],
            'beautician_id' => ['nullable'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function assertNoOverlappingSchedules(array $lines, \Closure $fail): void
    {
        $spaBranchId = (int) $this->input('spa_branch_id');
        $availability = app('modules')->isEnabled('TreatmentReservation')
            ? app(\Modules\TreatmentReservation\Services\AppointmentAvailabilityService::class)
            : null;
        $normalize = app('modules')->isEnabled('TreatmentReservation')
            ? app(\Modules\TreatmentReservation\Services\BeauticianAvailabilityService::class)
            : null;

        $windows = [];

        foreach ($lines as $index => $line) {
            if (filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $beauticianId = (int) ($line['beautician_id'] ?? 0);
            $date = (string) ($line['appointment_date'] ?? '');
            $time = $normalize
                ? ($normalize->normalizeTime((string) ($line['appointment_time'] ?? '')) ?? '')
                : substr((string) ($line['appointment_time'] ?? ''), 0, 5);
            $productId = (int) ($line['product_id'] ?? 0);

            if (! $beauticianId || $date === '' || $time === '') {
                continue;
            }

            $duration = 60;
            if ($availability && $productId && $spaBranchId) {
                $duration = max(1, $availability->resolveDurationMinutes($productId, $spaBranchId));
            }

            [$h, $m] = array_map('intval', explode(':', $time));
            $startMin = ($h * 60) + $m;
            $endMin = $startMin + $duration;

            foreach ($windows as $other) {
                if (
                    $other['beautician_id'] === $beauticianId
                    && $other['date'] === $date
                    && $startMin < $other['end']
                    && $endMin > $other['start']
                ) {
                    $fail(trans('checkout::messages.treatment_schedule_overlap'));

                    return;
                }
            }

            $windows[] = [
                'beautician_id' => $beauticianId,
                'date' => $date,
                'start' => $startMin,
                'end' => $endMin,
                'index' => $index,
            ];
        }
    }

    private function resolveCartTreatmentProductId(): ?int
    {
        foreach (Cart::items() as $item) {
            if ($item->product && $item->product->isVirtualTreatment()) {
                return (int) $item->product->id;
            }
        }

        return null;
    }


    private function spaBranchRules(): array
    {
        if (! Cart::hasVirtualTreatment()) {
            return [];
        }

        if (! app('modules')->isEnabled('SpaBranch')) {
            return [];
        }

        if (! \Modules\SpaBranch\Entities\SpaBranch::query()->where('is_active', true)->exists()) {
            return [];
        }

        return [
            'spa_branch_id' => [
                'required',
                Rule::exists('spa_branches', 'id')->where('is_active', true),
            ],
        ];
    }


    private function emailUniqueRule()
    {
        return $this->create_an_account ? Rule::unique('users', 'email') : null;
    }


    private function billingAddressRules()
    {
        return [
            'billing.first_name' => ['required', 'string', 'max:255'],
            'billing.last_name' => ['required', 'string', 'max:255'],
            'billing.address_1' => ['required', 'string', 'max:255'],
            'billing.address_2' => ['nullable', 'string', 'max:255'],
            'billing.city' => ['required', 'string', 'max:255'],
            'billing.zip' => ['required', 'string', 'max:255'],
            'billing.country' => ['required', Rule::in(Country::supportedCodes())],
            'billing.state' => ['required', 'string', 'max:255'],
        ];
    }


    private function shippingAddressRules()
    {
        return [
            'shipping.first_name' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
            'shipping.last_name' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
            'shipping.address_1' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
            'shipping.address_2' => ['nullable', 'string', 'max:255'],
            'shipping.city' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
            'shipping.zip' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
            'shipping.country' => ['required_if:ship_to_a_different_address,1', Rule::in(Country::supportedCodes())],
            'shipping.state' => ['required_if:ship_to_a_different_address,1', 'nullable', 'string', 'max:255'],
        ];
    }
}
