<?php

namespace AestheticCart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Services\MaintenanceModeService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as BaseCheckForMaintenanceMode;

class CheckForMaintenanceMode extends BaseCheckForMaintenanceMode
{
    /**
     * The URIs that should be accessible while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        'admin',
        'admin/*',
        'countries/*',
        'favicon.ico',
    ];


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws HttpException
     * @throws MaintenanceModeException
     */
    public function handle($request, Closure $next): mixed
    {
        if (class_exists(MaintenanceModeService::class)) {
            $this->except = app(MaintenanceModeService::class)->excludedPaths();
        }

        return parent::handle($request, $next);
    }
}
