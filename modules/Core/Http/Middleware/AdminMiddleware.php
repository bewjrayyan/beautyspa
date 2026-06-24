<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use AestheticCart\Http\IntendedUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminMiddleware
{
    /**
     * The routes that should be excluded from verification.
     *
     * @var array
     */
    protected $except = [
        'admin.login.*',
        'admin.reset.*',
        'admin.locale.switch',
    ];


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isCustomer()) {
            return redirect()->route('account.dashboard.index');
        }

        if ($this->inExceptArray($request) || auth()->check()) {
            $this->refreshMaintenanceTemplateIfNeeded();

            return $next($request);
        }

        $intended = $request->isMethod('GET') && $request->route() && ! $request->expectsJson()
            ? url()->full()
            : url()->previous();

        if ($intended) {
            session()->put('url.intended', IntendedUrl::normalize($intended));
        }

        return redirect()->route('admin.login');
    }


    /**
     * Determine if the request URI is in except array.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            $routeName = optional($request->route())->getName();

            if (preg_match("/{$except}/", $routeName)) {
                return true;
            }
        }

        return false;
    }


    private function refreshMaintenanceTemplateIfNeeded(): void
    {
        if (! config('app.installed') || ! app()->isDownForMaintenance()) {
            return;
        }

        if (! class_exists(\Modules\Setting\Services\MaintenanceModeService::class)) {
            return;
        }

        try {
            app(\Modules\Setting\Services\MaintenanceModeService::class)->refreshBrandedTemplateIfNeeded();
        } catch (\Throwable) {
            // Do not block admin access when maintenance refresh fails.
        }
    }
}
