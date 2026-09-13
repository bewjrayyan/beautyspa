<?php

namespace Tests\Unit\Coupon;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Coupon\Http\Requests\SaveCouponRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaveCouponRequestTest extends TestCase
{
    #[Test]
    public function it_normalizes_codes_and_accepts_an_end_only_schedule(): void
    {
        $request = $this->request([
            'code' => 'launch-' . Str::lower(Str::random(10)),
            'end_date' => '2026-12-31',
        ]);

        $request->validateResolved();

        $this->assertSame(strtoupper($request->input('code')), $request->input('code'));
    }

    #[Test]
    public function it_rejects_unsafe_discount_schedule_spend_and_redemption_rules(): void
    {
        $request = $this->request([
            'is_percent' => 1,
            'value' => 101,
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01',
            'minimum_spend' => 500,
            'maximum_spend' => 100,
            'usage_limit_per_coupon' => 2,
            'usage_limit_per_customer' => 3,
        ]);

        try {
            $request->validateResolved();
            $this->fail('Expected coupon validation to fail.');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $this->assertArrayHasKey('value', $errors);
            $this->assertArrayHasKey('end_date', $errors);
            $this->assertArrayHasKey('maximum_spend', $errors);
            $this->assertArrayHasKey('usage_limit_per_customer', $errors);
        }
    }

    private function request(array $overrides = []): SaveCouponRequest
    {
        $request = SaveCouponRequest::create('/admin/coupons', 'POST', array_merge([
            'name' => 'Launch Campaign',
            'code' => 'LAUNCH-' . strtoupper(Str::random(10)),
            'is_percent' => 0,
            'value' => 10,
            'free_shipping' => 0,
            'is_active' => 1,
        ], $overrides));

        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        return $request;
    }
}
