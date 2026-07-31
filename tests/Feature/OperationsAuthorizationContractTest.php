<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationsAuthorizationContractTest extends TestCase
{
    #[Test]
    public function operations_routes_keep_separate_view_queue_and_retention_permissions(): void
    {
        $expectations = [
            'admin.operations.index' => 'can:admin.operations.view',
            'admin.operations.queue.cancel' => 'can:admin.operations.manage_queue',
            'admin.operations.failed.retry' => 'can:admin.operations.manage_queue',
            'admin.operations.legal_hold.place' => 'can:admin.operations.manage_retention',
            'admin.operations.legal_hold.release' => 'can:admin.operations.manage_retention',
        ];

        foreach ($expectations as $name => $permission) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertContains($permission, $route->gatherMiddleware());
        }
    }

    #[Test]
    public function operations_view_compiles_without_executing_sensitive_queries(): void
    {
        $source = file_get_contents(
            base_path('modules/Setting/Resources/views/admin/operations/index.blade.php')
        );

        $compiled = Blade::compileString($source);

        $this->assertStringContainsString('admin.operations.queue.cancel', $compiled);
        $this->assertStringContainsString('admin.operations.legal_hold.place', $compiled);
        $this->assertStringNotContainsString('payload', $compiled);
        $this->assertStringNotContainsString('exception', $compiled);
    }
}
