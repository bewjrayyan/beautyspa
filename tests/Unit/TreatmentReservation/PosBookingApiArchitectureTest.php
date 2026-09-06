<?php

namespace Tests\Unit\TreatmentReservation;

use PHPUnit\Framework\TestCase;

class PosBookingApiArchitectureTest extends TestCase
{
    public function test_pos_api_contract_is_registered_and_protected(): void
    {
        $root = dirname(__DIR__, 3);
        $routes = file_get_contents($root . '/modules/TreatmentReservation/Routes/api.php');
        $provider = file_get_contents($root . '/modules/Core/Providers/RouteServiceProvider.php');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Api/BookingController.php');

        self::assertStringContainsString("Route::post('/', [BookingController::class, 'store'])", $routes);
        self::assertStringContainsString("Route::get('availability'", $routes);
        self::assertStringContainsString("Route::get('available-dates'", $routes);
        self::assertStringContainsString("'middleware' => ['web', 'auth']", $provider);
        self::assertStringContainsString("Gate::authorize('view'", $controller);
        self::assertStringContainsString('$request->user()->isBeauticianOnly()', $controller);
    }

    public function test_pos_uses_guided_per_treatment_availability_flow(): void
    {
        $root = dirname(__DIR__, 3);
        $script = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/pos.js');
        $request = file_get_contents($root . '/modules/TreatmentReservation/Http/Requests/StorePosBookingRequest.php');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Admin/PosController.php');

        self::assertStringContainsString("['Variant', 'Branch', 'Beautician', 'Date', 'Time']", $script);
        self::assertStringContainsString('/bookings/available-dates?', $script);
        self::assertStringContainsString('/bookings/availability?', $script);
        self::assertStringContainsString('data-pos-edit-line', $script);
        self::assertStringContainsString('data-wizard-option', $script);
        self::assertStringContainsString("prefix}[options]", $script);
        self::assertStringContainsString("'options' => \$product->options->map", $controller);
        self::assertStringContainsString("'required_without:items'", $request);
        self::assertStringContainsString("'items.*.appointment_date'", $request);
    }

    public function test_pos_domain_persists_customer_and_audit_contracts(): void
    {
        $root = dirname(__DIR__, 3);
        $model = file_get_contents($root . '/modules/TreatmentReservation/Entities/TreatmentBooking.php');
        $migration = file_get_contents($root . '/modules/TreatmentReservation/Database/Migrations/2026_09_06_000001_add_customer_id_to_treatment_bookings.php');
        $service = file_get_contents($root . '/modules/TreatmentReservation/Services/PosBookingService.php');
        $controller = file_get_contents($root . '/modules/TreatmentReservation/Http/Controllers/Api/BookingController.php');

        self::assertStringContainsString("'customer_id'", $model);
        self::assertStringContainsString('function customer()', $model);
        self::assertStringContainsString('$table->foreign(\'customer_id\')', $migration);
        self::assertStringContainsString('logStatusChange', $service);
        self::assertStringContainsString('isManualBooking', $service);
        self::assertStringContainsString("'_schedule_holds' => \$customerHolds", $service);
        self::assertStringContainsString('$customerHolds', $service);
        self::assertStringContainsString('slotConflictsWithHolds', $controller);
    }

    public function test_pos_enforces_offline_payment_receipt_and_bounded_cart(): void
    {
        $root = dirname(__DIR__, 3);
        $request = file_get_contents($root . '/modules/TreatmentReservation/Http/Requests/StorePosBookingRequest.php');
        $updateRequest = file_get_contents($root . '/modules/TreatmentReservation/Http/Requests/UpdatePosBookingRequest.php');
        $script = file_get_contents($root . '/modules/TreatmentReservation/Resources/assets/admin/js/pos.js');
        $service = file_get_contents($root . '/modules/TreatmentReservation/Services/PosBookingService.php');
        $availability = file_get_contents($root . '/modules/TreatmentReservation/Services/AppointmentAvailabilityService.php');

        self::assertStringContainsString("Rule::in([TreatmentBooking::PAYMENT_FULL_PAID])", $request);
        self::assertStringContainsString("'payment_receipt' => ['required', 'file'", $request);
        self::assertStringContainsString("'items' => ['nullable', 'array', 'min:1', 'max:20']", $request);
        self::assertStringContainsString("Rule::in([TreatmentBooking::PAYMENT_FULL_PAID])", $updateRequest);
        self::assertStringContainsString("'payment_receipt' => ['sometimes', 'file'", $updateRequest);
        self::assertStringContainsString('new FormData()', $script);
        self::assertStringNotContainsString("'Content-Type': 'application/json'", $script);
        self::assertStringContainsString('PAYMENT_FULL_PAID', $service);
        self::assertStringContainsString('customerHasConflict', $availability);
        self::assertStringContainsString('customer:', $availability);
    }
}
