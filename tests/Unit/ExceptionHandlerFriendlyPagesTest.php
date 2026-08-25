<?php

namespace Tests\Unit;

use AestheticCart\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ExceptionHandlerFriendlyPagesTest extends TestCase
{
    #[Test]
    public function url_generation_errors_render_friendly_pages_even_when_debug_is_on(): void
    {
        config([
            'app.debug' => true,
            'app.show_detailed_errors' => false,
        ]);

        $handler = $this->app->make(Handler::class);
        $request = Request::create('/en/consultation/demo/account', 'GET');
        $request->headers->set('Accept', 'text/html');

        $route = new Route(['GET'], 'consultation/{token}/account', fn () => null);
        $route->bind($request);

        $exception = new UrlGenerationException(
            'Missing required parameter for [Route: consultations.lookup] [URI: en/consultation/{token}/account] [Missing parameter: token].'
        );

        $response = $handler->render($request, $exception);

        $this->assertSame(404, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertStringNotContainsString('Ignition', $content);
        $this->assertStringNotContainsString('UrlGenerationException', $content);
        $this->assertStringNotContainsString('Missing required parameter', $content);
        $this->assertTrue(
            str_contains($content, 'error-card')
            || str_contains($content, 'error-page')
            || str_contains($content, trans('errors.404.title'))
            || str_contains($content, trans('storefront::404.page_not_found'))
        );
    }

    #[Test]
    public function http_exceptions_use_branded_error_views(): void
    {
        config([
            'app.debug' => true,
            'app.show_detailed_errors' => false,
        ]);

        $handler = $this->app->make(Handler::class);
        $request = Request::create('/admin/missing', 'GET');
        $request->headers->set('Accept', 'text/html');

        $response = $handler->render($request, new HttpException(500, 'boom'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString(trans('errors.500.title'), $response->getContent());
        $this->assertStringNotContainsString('boom', $response->getContent());
    }
}
