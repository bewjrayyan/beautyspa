<?php

namespace AestheticCart\Exceptions;

use Throwable;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Swift_TransportException;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Modules\Sms\Exceptions\SmsException;
use Modules\Support\Cache\CacheHealth;
use Modules\TreatmentReservation\Support\TreatmentSlotConflict;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\NamespaceNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        CommandNotFoundException::class,
        NamespaceNotFoundException::class,
        ThrottlingException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];


    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $e
     *
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        if (TreatmentSlotConflict::causedBy($e)) {
            return;
        }

        if ($this->shouldSkipReportingOnLocalStorageFailure($e)) {
            return;
        }

        if ($this->shouldSkipNoisyLocalReporting($e)) {
            return;
        }

        parent::report($e);
    }

    private function shouldSkipNoisyLocalReporting(Throwable $e): bool
    {
        if (! app()->environment('local')) {
            return false;
        }

        return str_contains($e->getMessage(), 'Vite manifest not found');
    }

    private function shouldSkipReportingOnLocalStorageFailure(Throwable $e): bool
    {
        if (! app()->environment('local')) {
            return false;
        }

        $message = $e->getMessage();

        if (
            $e instanceof \UnexpectedValueException
            && str_contains($message, 'could not be opened in append mode')
        ) {
            return true;
        }

        if (
            ($e instanceof \ErrorException || $e instanceof \UnexpectedValueException)
            && str_contains($message, 'Permission denied')
            && (
                str_contains($message, 'storage/logs')
                || str_contains($message, 'storage/framework/sessions')
                || str_contains($message, 'file_put_contents')
            )
        ) {
            return true;
        }

        return false;
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     *
     * @return Response|JsonResponse|RedirectResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $special = match (true) {
            $e instanceof ThrottlingException => $this->handleThrottlingException($request),
            TreatmentSlotConflict::causedBy($e) => response()->json([
                'message' => trans('treatmentreservation::public.slot_unavailable'),
            ], Response::HTTP_CONFLICT),
            $e instanceof Swift_TransportException => $this->handleSwiftException($request, $e),
            $e instanceof SmsException => $this->handleSmsException($request, $e),
            $e instanceof ValidationException && $request->ajax() => response()->json([
                'message' => trans('core::messages.the_given_data_was_invalid'),
                'errors' => $e->validator->getMessageBag(),
            ], 422),
            $e instanceof \Modules\Checkout\Exceptions\CheckoutException => response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN),
            default => null,
        };

        if ($special !== null) {
            return $special;
        }

        // Keep Laravel form/auth redirect behaviour for browser flows.
        if (
            $e instanceof ValidationException
            || $e instanceof AuthenticationException
        ) {
            return parent::render($request, $e);
        }

        if (CacheHealth::isRedisAuthOrConnectivityFailure($e)) {
            CacheHealth::fallbackFromRedis();

            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'message' => trans('core::messages.something_went_wrong'),
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return $this->renderFriendlyHtmlError($e);
        }

        if ($this->shouldRenderFriendlyHtmlError($request)) {
            return $this->renderFriendlyHtmlError($e);
        }

        return parent::render($request, $e);
    }


    /**
     * Browser visitors always get branded friendly pages unless Ignition is
     * explicitly opted in via SHOW_DETAILED_ERRORS=true with APP_DEBUG=true.
     */
    private function shouldRenderFriendlyHtmlError(Request $request): bool
    {
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return false;
        }

        if (config('app.debug') && config('app.show_detailed_errors')) {
            return false;
        }

        return true;
    }


    private function renderFriendlyHtmlError(Throwable $e): Response
    {
        $status = $this->friendlyStatusCode($e);
        $view = $this->friendlyViewForStatus($status);

        return response()->view($view, [
            'exception' => $e,
        ], $status);
    }


    private function friendlyStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return max(400, min(599, $e->getStatusCode()));
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return Response::HTTP_NOT_FOUND;
        }

        if ($e instanceof AuthorizationException) {
            return Response::HTTP_FORBIDDEN;
        }

        if ($e instanceof TokenMismatchException) {
            return 419;
        }

        if ($e instanceof UrlGenerationException) {
            // Broken internal links / missing route params — do not expose Ignition.
            return Response::HTTP_NOT_FOUND;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }


    private function friendlyViewForStatus(int $status): string
    {
        if (
            $status === Response::HTTP_NOT_FOUND
            && ! $this->inAdminPanel()
            && view()->exists('storefront::errors.404')
        ) {
            return 'storefront::errors.404';
        }

        $named = "errors.{$status}";

        if (view()->exists($named)) {
            return $named;
        }

        if ($status >= 500 && view()->exists('errors.5xx')) {
            return 'errors.5xx';
        }

        if ($status >= 400 && view()->exists('errors.4xx')) {
            return 'errors.4xx';
        }

        return view()->exists('errors.500') ? 'errors.500' : 'errors.4xx';
    }


    /**
     * Render Sentinel throttling as a safe, customer-friendly response.
     */
    private function handleThrottlingException(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => trans('errors.security_throttle.message'),
                'next_step' => trans('errors.security_throttle.guidance'),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return response()->view(
            'errors.security-throttle',
            [],
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }


    /**
     * Handle swift transport exception.
     *
     * @param Request $request
     * @param Swift_TransportException $e
     *
     * @return mixed
     *
     * @throws Swift_TransportException
     */
    private function handleSwiftException(Request $request, Swift_TransportException $e)
    {
        if (config('app.debug') && config('app.show_detailed_errors')) {
            throw $e;
        }

        if ($request->ajax()) {
            abort(400, trans('core::messages.mail_is_not_configured'));
        }

        return back()->withInput()
            ->with('error', trans('core::messages.mail_is_not_configured'));
    }


    /**
     * Handle sms exception.
     *
     * @param Request $request
     * @param SmsException $e
     *
     * @return RedirectResponse
     *
     * @throws SmsException
     */
    private function handleSmsException(Request $request, SmsException $e)
    {
        if (config('app.debug') && config('app.show_detailed_errors')) {
            throw $e;
        }

        if ($request->ajax()) {
            abort(400, $e->getMessage());
        }

        return back()->withInput()->with('error', $e->getMessage());
    }


    /**
     * Determine if the request is from admin panel.
     *
     * @return bool
     */
    private function inAdminPanel(): bool
    {
        return $this->container->has('inAdminPanel') && $this->container['inAdminPanel'];
    }
}
