<?php

namespace Tests\Unit\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubdirectorySignedRouteTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = require dirname(__DIR__, 3).'/bootstrap/app.php';
        $this->application->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        Facade::setFacadeApplication($this->application);
    }

    protected function tearDown(): void
    {
        Facade::setFacadeApplication(null);
        \AestheticCart\Http\FixSubdirectoryRequest::resetResolvedState();
        \Illuminate\Foundation\Bootstrap\HandleExceptions::flushState($this);

        parent::tearDown();
    }

    #[Test]
    public function it_resigns_install_base_from_web_generated_signed_paths(): void
    {
        $_SERVER['REQUEST_URI'] = '/fleetcart/admin/orders/1400';
        $_SERVER['SCRIPT_NAME'] = '/fleetcart/public/index.php';
        $_SERVER['HTTP_HOST'] = 'localhost';

        \AestheticCart\Http\FixSubdirectoryRequest::apply();

        $relative = aestheticcart_subdirectory_safe_temporary_signed_route(
            'order.payment_proofs.temporary',
            now()->addMinutes(90),
            ['order' => 1400, 'file' => 219]
        );

        $this->assertStringStartsWith('/secure/order/1400/payment-proof/219?', $relative);
        $this->assertStringNotContainsString('/fleetcart/secure/', $relative);

        $request = \Illuminate\Http\Request::create($relative, 'GET');
        $this->assertTrue($request->hasValidSignature(absolute: false));
    }

    #[Test]
    public function arrival_pass_signature_survives_the_install_base_being_stripped(): void
    {
        $_SERVER['REQUEST_URI'] = '/fleetcart/en/my-appointments';
        $_SERVER['SCRIPT_NAME'] = '/fleetcart/public/index.php';
        $_SERVER['HTTP_HOST'] = 'localhost';

        \AestheticCart\Http\FixSubdirectoryRequest::apply();

        $booking = new \Modules\TreatmentReservation\Entities\TreatmentBooking();
        $booking->forceFill([
            'id' => 1033,
            'appointment_date' => now()->addDay()->toDateString(),
        ]);

        $url = app(\Modules\TreatmentReservation\Services\BookingCheckinPassService::class)
            ->url($booking);
        $path = parse_url($url, PHP_URL_PATH);

        $this->assertStringStartsWith(
            'http://localhost/fleetcart/appointment-check-in/1033?',
            $url
        );
        $this->assertIsString($path);
        $request = \Illuminate\Http\Request::create(
            substr($path, strlen('/fleetcart')).'?'.parse_url($url, PHP_URL_QUERY),
            'GET'
        );

        $middleware = app(\AestheticCart\Http\Middleware\ValidateSubdirectoryRelativeSignature::class);
        $response = $middleware->handle($request, fn () => response('ok'), 'relative');

        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_accepts_an_existing_absolute_arrival_pass_signature_after_base_stripping(): void
    {
        $_SERVER['REQUEST_URI'] = '/fleetcart/en/my-appointments';
        $_SERVER['SCRIPT_NAME'] = '/fleetcart/public/index.php';
        $_SERVER['HTTP_HOST'] = 'localhost';

        \AestheticCart\Http\FixSubdirectoryRequest::apply();
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost/fleetcart');
        $legacyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'treatment_reservations.checkin.pass',
            now()->addHour(),
            ['booking' => 1033]
        );
        $parts = parse_url($legacyUrl);
        $strippedPath = substr((string) $parts['path'], strlen('/fleetcart'));
        $request = \Illuminate\Http\Request::create(
            $strippedPath.'?'.($parts['query'] ?? ''),
            'GET'
        );

        $middleware = app(\AestheticCart\Http\Middleware\ValidateSubdirectoryRelativeSignature::class);
        $response = $middleware->handle($request, fn () => response('ok'), 'relative');

        $this->assertSame(200, $response->getStatusCode());
    }
}
