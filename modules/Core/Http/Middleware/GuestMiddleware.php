<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Already logged in.',
                    'redirect' => app()->bound('inAdminPanel') && app('inAdminPanel')
                        ? auth()->user()->adminHomeRoute()
                        : route('account.dashboard.index'),
                ], 409);
            }

            if (app('inAdminPanel')) {
                return redirect()->to(auth()->user()->adminHomeRoute());
            }

            return redirect()->route('account.dashboard.index');
        }

        return $next($request);
    }
}
