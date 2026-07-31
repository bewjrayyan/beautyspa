<?php

namespace Tests\Unit\Beautician;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BeauticianSelfRegistrationContractTest extends TestCase
{
    #[Test]
    public function public_registration_is_separate_from_the_privileged_admin_create_route(): void
    {
        $publicRoutes = file_get_contents(
            dirname(__DIR__, 3).'/modules/Beautician/Routes/public.php'
        );
        $adminRoutes = file_get_contents(
            dirname(__DIR__, 3).'/modules/Beautician/Routes/admin.php'
        );

        $this->assertStringContainsString("Route::get('beauticians/register'", $publicRoutes);
        $this->assertStringContainsString('ProtectAgainstSpam::class', $publicRoutes);
        $this->assertStringContainsString("'throttle:forms'", $publicRoutes);
        $this->assertStringContainsString("'middleware' => 'can:admin.beauticians.create'", $adminRoutes);
    }


    #[Test]
    public function self_registration_creates_a_pending_profile_without_customer_or_admin_access(): void
    {
        $controller = file_get_contents(
            dirname(__DIR__, 3).'/modules/Beautician/Http/Controllers/BeauticianRegistrationController.php'
        );

        $this->assertStringContainsString("Role::whereTranslation('name', 'Beautician')", $controller);
        $this->assertStringContainsString("'is_active' => false", $controller);
        $this->assertStringContainsString('DB::transaction', $controller);
        $this->assertStringNotContainsString('customer_role', $controller);
        $this->assertStringNotContainsString("'is_active' => true", $controller);
    }


    #[Test]
    public function applicants_cannot_choose_an_inactive_branch_or_arbitrary_job_title(): void
    {
        $request = file_get_contents(
            dirname(__DIR__, 3).'/modules/Beautician/Http/Requests/RegisterBeauticianRequest.php'
        );

        $this->assertStringContainsString('JobTitleOptions::activeNames()', $request);
        $this->assertStringContainsString("Rule::exists('spa_branches', 'id')->where('is_active', true)", $request);
        $this->assertStringContainsString("['required', 'array', 'min:1']", $request);
        $this->assertStringContainsString("Rule::unique('users', 'email')", $request);
    }


    #[Test]
    public function pending_beauticians_are_redirected_away_from_admin_routes_but_can_logout(): void
    {
        $middleware = file_get_contents(
            dirname(__DIR__, 3).'/modules/TreatmentReservation/Http/Middleware/RestrictBeauticianPortalMiddleware.php'
        );

        $this->assertStringContainsString('hasPendingBeauticianProfile()', $middleware);
        $this->assertStringContainsString("=== 'admin.logout'", $middleware);
        $this->assertStringContainsString("route('beauticians.registration.pending')", $middleware);
    }
}
