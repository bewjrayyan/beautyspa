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
}
