<?php

namespace Tests\Unit\Support;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Modules\Account\Jobs\GenerateConsultationPdf;
use Modules\GoogleIntegration\Jobs\SyncOrderToGoogleJob;
use Modules\Payment\Jobs\ProcessChipWebhookPurchase;
use Modules\Setting\Http\Controllers\Admin\OperationsController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class OperationalSecurityContractTest extends TestCase
{
    #[Test]
    public function sensitive_consultation_routes_keep_auth_ownership_and_permission_controls(): void
    {
        $root = dirname(__DIR__, 3);
        $publicRoutes = file_get_contents($root.'/modules/Account/Routes/public.php');
        $customerController = file_get_contents($root.'/modules/Account/Http/Controllers/AccountConsultationController.php');
        $adminRoutes = file_get_contents($root.'/modules/User/Routes/admin.php');

        $this->assertStringContainsString("Route::middleware('auth')->group", $publicRoutes);
        $this->assertStringContainsString('$this->customerAccess->claim($submission, $request->user())', $customerController);
        $this->assertStringContainsString('can:admin.consultation_submissions.download', $adminRoutes);
        $this->assertStringContainsString('throttle:20,1', $adminRoutes);
    }

    #[Test]
    public function public_security_entry_points_keep_rate_limit_and_signature_verification(): void
    {
        $root = dirname(__DIR__, 3);
        $consultationRoutes = file_get_contents($root.'/modules/Account/Routes/public.php');
        $paymentController = file_get_contents($root.'/modules/Payment/Http/Controllers/ChipWebhookController.php');
        $webRoutes = file_get_contents($root.'/routes/web.php');

        $this->assertStringContainsString("->middleware('throttle:10,1')", $consultationRoutes);
        $this->assertStringContainsString('ChipWebhookSignatureVerifier', $paymentController);
        $this->assertStringContainsString('$verifier->verify($rawBody, $signature)', $paymentController);
        $this->assertStringContainsString("->middleware('throttle:60,1')", $webRoutes);
    }

    #[Test]
    public function externally_visible_queue_jobs_have_duplicate_and_timeout_guards(): void
    {
        $pdf = new GenerateConsultationPdf(123);
        $google = new SyncOrderToGoogleJob(456);
        $chip = new ProcessChipWebhookPurchase('purchase-789');

        $this->assertInstanceOf(ShouldBeUnique::class, $pdf);
        $this->assertSame('123', $pdf->uniqueId());
        $this->assertSame(120, $pdf->timeout);
        $this->assertInstanceOf(ShouldBeUniqueUntilProcessing::class, $google);
        $this->assertSame('456:normal', $google->uniqueId());
        $this->assertSame([30, 120, 300], $google->backoff);
        $this->assertInstanceOf(ShouldBeUnique::class, $chip);
        $this->assertSame(60, $chip->timeout);
        $this->assertSame([15, 60, 180, 600], $chip->backoff);
    }

    #[Test]
    public function operations_dashboard_extracts_only_the_safe_job_class_name(): void
    {
        $method = new ReflectionMethod(OperationsController::class, 'jobDisplayName');
        $payload = json_encode([
            'displayName' => 'Modules\\Payment\\Jobs\\ProcessChipWebhookPurchase',
            'data' => ['command' => 'serialized-secret-payload'],
        ]);

        $name = $method->invoke(new OperationsController(), $payload);

        $this->assertSame('ProcessChipWebhookPurchase', $name);
        $this->assertStringNotContainsString('secret', $name);
    }

    #[Test]
    public function order_notification_listeners_run_after_commit_with_critical_group_messages_not_queue_dependent(): void
    {
        $queuedListeners = [
            \Modules\Order\Listeners\SendOrderStatusChangedEmail::class,
            \Modules\Loyalty\Listeners\ProcessLoyaltyOnOrderStatusChanged::class,
            \Modules\Loyalty\Listeners\AwardStampsOnOrderPlaced::class,
            \Modules\TreatmentReservation\Listeners\SyncTreatmentBookingFromOrder::class,
        ];

        foreach ($queuedListeners as $listener) {
            $this->assertTrue(
                is_subclass_of($listener, \Illuminate\Contracts\Queue\ShouldQueueAfterCommit::class),
                $listener.' must implement ShouldQueueAfterCommit'
            );
        }

        $directWhatsAppListeners = [
            \Modules\Checkout\Listeners\SendNewOrderSms::class,
            \Modules\Order\Listeners\SendOrderStatusChangedSms::class,
            \Modules\Order\Listeners\SendCompletedOrderGroupWhatsApp::class,
            \Modules\Order\Listeners\SendCompletedOrderBeauticianWhatsApp::class,
            \Modules\Order\Listeners\SendBankTransferPaymentProofWhatsApp::class,
        ];

        foreach ($directWhatsAppListeners as $listener) {
            $this->assertTrue(
                is_subclass_of($listener, \Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit::class),
                $listener.' must run after commit without requiring a queue worker'
            );
        }
    }

    #[Test]
    public function whatsapp_delivery_keeps_retry_and_configuration_guards(): void
    {
        $root = dirname(__DIR__, 3);
        $service = file_get_contents($root.'/modules/User/Services/OneSenderWhatsAppService.php');
        $queue = file_get_contents($root.'/modules/User/Services/OneSenderOutboundQueueService.php');
        $settings = file_get_contents($root.'/modules/Setting/Support/WhatsAppNotificationDefaults.php');
        $request = file_get_contents($root.'/modules/Setting/Http/Requests/UpdateSettingRequest.php');

        $this->assertStringContainsString("'fallback_to_queue'", $service);
        $this->assertStringContainsString('$queueService->defer($queued', $service);
        $this->assertStringContainsString('public function defer(', $queue);
        $this->assertStringContainsString('public function expireStale()', $queue);
        $this->assertStringContainsString('$this->expireStale();', $queue);
        $this->assertStringContainsString('return ! Setting::has($key);', $settings);
        $this->assertStringContainsString("'required_if:whatsapp_completed_group_enabled,1'", $request);
        $this->assertStringContainsString("'required_if:bank_transfer_payment_proof_whatsapp_enabled,1'", $request);
    }
}
