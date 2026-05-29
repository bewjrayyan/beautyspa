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
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            if (app('inAdminPanel')) {
                return redirect()->to(auth()->user()->adminHomeRoute());
            }

            return redirect()->route('account.dashboard.index');
        }

        return $next($request);
    }
}
