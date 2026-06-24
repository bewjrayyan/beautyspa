<?php

namespace Modules\Setting\Services;

use AestheticCart\Http\FixSubdirectoryRequest;
use Illuminate\Support\Facades\View;
use Modules\Setting\Support\MaintenancePageSettings;

class MaintenanceModeService
{
    private const BRANDED_TEMPLATE_MARKER = 'maintenance-bokeh';


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
            @unlink($bootstrap);
        }
    }


    /**
     * Re-prerender the branded page when maintenance was enabled before v4.9.9
     * or when except paths change (e.g. /v2 subdirectory installs).
     */
    public function refreshBrandedTemplateIfNeeded(): bool
    {
        if (! app()->isDownForMaintenance()) {
            return false;
        }

        if ($this->hasBrandedTemplate() && $this->exceptPathsMatch() && $this->visualSettingsMatch()) {
            return false;
        }

        $this->enable();

        return true;
    }


    private function hasBrandedTemplate(): bool
    {
        $template = $this->currentTemplate();

        return is_string($template) && str_contains($template, self::BRANDED_TEMPLATE_MARKER);
    }


    private function visualSettingsMatch(): bool
    {
        $template = $this->currentTemplate();

        if (! is_string($template)) {
            return false;
        }

        return str_contains($template, '<!-- maintenance-fx:'.MaintenancePageSettings::fingerprint().' -->');
    }


    private function exceptPathsMatch(): bool
    {
        try {
            $current = array_values((array) (app()->maintenanceMode()->data()['except'] ?? []));
        } catch (\Throwable) {
            return false;
        }

        sort($current);

        $expected = $this->excludedPaths();
        sort($expected);

        return $current === $expected;
    }


    private function currentTemplate(): ?string
    {
        try {
            $template = app()->maintenanceMode()->data()['template'] ?? null;
        } catch (\Throwable) {
            return null;
        }

        return is_string($template) ? $template : null;
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
        try {
            $stub = base_path('vendor/laravel/framework/src/Illuminate/Foundation/Console/stubs/maintenance-mode.stub');

            if (! is_readable($stub)) {
                return;
            }

            @file_put_contents(storage_path('framework/maintenance.php'), file_get_contents($stub));
        } catch (\Throwable) {
            // Middleware can still serve the prerendered template from the down file.
        }
    }
}
