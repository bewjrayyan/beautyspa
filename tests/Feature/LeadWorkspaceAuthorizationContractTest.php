<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadWorkspaceAuthorizationContractTest extends TestCase
{
    #[Test]
    public function lead_workspace_routes_keep_index_show_create_and_edit_permissions(): void
    {
        $expectations = [
            'admin.leads.central' => 'can:admin.leads.index',
            'admin.leads.reporting' => 'can:admin.leads.index',
            'admin.leads.central.metrics' => 'can:admin.leads.index',
            'admin.leads.payments.index' => 'can:admin.leads.index',
            'admin.leads.customers.index' => 'can:admin.leads.index',
            'admin.leads.wallet.index' => 'can:admin.leads.index',
            'admin.leads.checkin.index' => 'can:admin.leads.index',
            'admin.leads.checkin.confirm' => 'can:admin.leads.edit',
            'admin.leads.clearance.index' => 'can:admin.leads.index',
            'admin.leads.workspace.index' => 'can:admin.leads.index',
            'admin.leads.workspace.store' => 'can:admin.leads.create',
            'admin.leads.workspace.bulk-update' => 'can:admin.leads.edit',
            'admin.leads.workspace.bulk-delete' => 'can:admin.leads.destroy',
            'admin.leads.workspace.show' => 'can:admin.leads.show',
            'admin.leads.workspace.status' => 'can:admin.leads.edit',
            'admin.leads.workspace.update' => 'can:admin.leads.edit',
            'admin.leads.workspace.destroy' => 'can:admin.leads.destroy',
            'admin.leads.followup.index' => 'can:admin.leads.index',
            'admin.leads.workspace.followup' => 'can:admin.leads.edit',
            'admin.leads.import.index' => 'can:admin.leads.index',
            'admin.leads.import.preview' => 'can:admin.leads.create',
            'admin.leads.import.confirm' => 'can:admin.leads.create',
        ];

        foreach ($expectations as $name => $permission) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route {$name}");
            $this->assertContains($permission, $route->gatherMiddleware());
        }

        $central = Route::getRoutes()->getByName('admin.leads.central');
        $this->assertMatchesRegularExpression('#leads/central/\{view\?\}$#', $central->uri());
        $this->assertArrayHasKey('view', $central->wheres);
        $this->assertStringContainsString('sales', (string) $central->wheres['view']);

        $pass = Route::getRoutes()->getByName('treatment_reservations.checkin.pass');
        $this->assertNotNull($pass);
        $this->assertContains('signed.subdirectory:relative', $pass->gatherMiddleware());
    }

    #[Test]
    public function lead_entity_exposes_stable_status_constants(): void
    {
        $statuses = \Modules\Lead\Entities\Lead::statuses();

        $this->assertContains('new', $statuses);
        $this->assertContains('follow_up', $statuses);
        $this->assertContains('converted', $statuses);
        $this->assertSame('FOLLOW-UP', (new \Modules\Lead\Entities\Lead(['status' => 'follow_up']))->status_label);
    }
}
