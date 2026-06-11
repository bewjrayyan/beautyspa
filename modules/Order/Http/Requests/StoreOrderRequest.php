<?php

namespace Modules\Order\Http\Requests;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Illuminate\Validation\Rule;
use Modules\Payment\Facades\Gateway;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;
use Modules\Checkout\Exceptions\CheckoutException;
use Modules\Beautician\Entities\Beautician;
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
                'payment_method' => ['required', Rule::in(Gateway::names())],
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

        $appointmentTimeRules = ['required', 'date_format:H:i'];

        if (app('modules')->isEnabled('TreatmentReservation')) {
            $appointmentTimeRules[] = new \Modules\TreatmentReservation\Rules\ValidBeauticianSlot();
        }

        return [
            'beautician_id' => [
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
            ],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => $appointmentTimeRules,
        ];
    }


    private function spaBranchRules(): array
    {
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
            'billing.first_name' => 'required',
            'billing.last_name' => 'required',
            'billing.address_1' => 'required',
            'billing.city' => 'required',
            'billing.zip' => 'required',
            'billing.country' => ['required', Rule::in(Country::supportedCodes())],
            'billing.state' => 'required',
        ];
    }


    private function shippingAddressRules()
    {
        return [
            'shipping.first_name' => 'required_if:ship_to_a_different_address,1',
            'shipping.last_name' => 'required_if:ship_to_a_different_address,1',
            'shipping.address_1' => 'required_if:ship_to_a_different_address,1',
            'shipping.city' => 'required_if:ship_to_a_different_address,1',
            'shipping.zip' => 'required_if:ship_to_a_different_address,1',
            'shipping.country' => ['required_if:ship_to_a_different_address,1', Rule::in(Country::supportedCodes())],
            'shipping.state' => 'required_if:ship_to_a_different_address,1',
        ];
    }
}
