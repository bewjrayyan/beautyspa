<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Contracts\Debug\ExceptionHandler;

class FriendlyThrottlingExceptionResponseTest extends TestCase
{
    public function test_html_request_receives_a_friendly_page_without_debug_details(): void
    {
        app()->setLocale('en');

        $request = Request::create('/en/account/orders/1394', 'GET');
        $exception = $this->throttlingException();

        $response = app(ExceptionHandler::class)->render($request, $exception);
        $content = $response->getContent();

        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertStringContainsString('Let’s pause for a moment', $content);
        $this->assertStringContainsString('Your account and order information remain safe.', $content);
        $this->assertStringNotContainsString('ThrottlingException', $content);
        $this->assertStringNotContainsString('1703 second', $content);
    }

    public function test_json_request_receives_a_safe_localized_message(): void
    {
        app()->setLocale('ms');

        $request = Request::create('/ms/account/orders/1394', 'GET', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response = app(ExceptionHandler::class)->render($request, $this->throttlingException());
        $payload = $response->getData(true);

        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertSame(
            'Kami mengesan beberapa percubaan log masuk daripada sambungan ini. Sebagai langkah berjaga-jaga, akses dihentikan sementara. Akaun dan maklumat pesanan anda kekal selamat.',
            $payload['message']
        );
        $this->assertArrayHasKey('next_step', $payload);
        $this->assertArrayNotHasKey('exception', $payload);
        $this->assertArrayNotHasKey('delay', $payload);
    }

    private function throttlingException(): ThrottlingException
    {
        $exception = new ThrottlingException(
            'Suspicious activity has occurred and access is denied for another [1703] seconds.'
        );
        $exception->setDelay(1703);

        return $exception;
    }
}
