<?php

namespace Modules\Setting\Services;

use AestheticCart\Http\FixSubdirectoryRequest;
use Illuminate\Support\Facades\View;

class MaintenanceModeService
{
    public function enable(): void
    {
        if (app()->isDownForMaintenance()) {
            app()->maintenanceMode()->deactivate();
        }

        $this->writeMaintenanceBootstrap();

        app()->maintenanceMode()->activate([
            'except' => $this->excludedPaths(),
            'redirect' => null,
            'retry' => 3600,
            'refresh' => null,
            'secret' => null,
            'status' => 503,
            'template' => View::make('storefront::errors.503')->render(),
        ]);
    }


    public function disable(): void
    {
        if (! app()->isDownForMaintenance()) {
            return;
        }

        app()->maintenanceMode()->deactivate();

        $bootstrap = storage_path('framework/maintenance.php');

        if (is_file($bootstrap)) {
            unlink($bootstrap);
        }
    }


    /**
     * @return array<int, string>
     */
    public function excludedPaths(): array
    {
        $base = trim(FixSubdirectoryRequest::basePath(), '/');
        $prefix = $base !== '' ? $base.'/' : '';

        return array_values(array_unique([
            $prefix.'admin',
            $prefix.'admin/*',
            'admin',
            'admin/*',
        ]));
    }


    private function writeMaintenanceBootstrap(): void
    {
        $stub = base_path('vendor/laravel/framework/src/Illuminate/Foundation/Console/stubs/maintenance-mode.stub');

        file_put_contents(storage_path('framework/maintenance.php'), file_get_contents($stub));
    }
}
