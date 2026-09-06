<?php

namespace Tests\Unit\Order;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Order\Http\Controllers\OrderPaymentProofController;
use Modules\Order\Http\Controllers\OrderTemporaryDocumentController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrivateDocumentAccessTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application(dirname(__DIR__, 4));
        Container::setInstance($this->application);
        Facade::setFacadeApplication($this->application);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstance('filesystem');
        Facade::setFacadeApplication(null);
        Container::setInstance(null);

        parent::tearDown();
    }

    #[Test]
    public function generated_documents_are_read_from_private_storage_with_no_store_headers(): void
    {
        $order = new Order();
        $order->setRawAttributes(['id' => 55, 'updated_at' => null], true);
        $fingerprint = md5('55');
        $path = "orders/55/invoice-{$fingerprint}.pdf";
        $response = new StreamedResponse();

        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())->method('exists')->with($path)->willReturn(true);
        $disk->expects($this->once())->method('response')->with(
            $path,
            'order-55-invoice.pdf',
            $this->callback(fn (array $headers): bool =>
                $headers['Cache-Control'] === 'private, no-store, max-age=0'
                && $headers['X-Content-Type-Options'] === 'nosniff'
            )
        )->willReturn($response);

        $this->bindFilesystem($disk);

        $actual = (new OrderTemporaryDocumentController())->show(
            $order,
            'invoice',
            $fingerprint
        );

        $this->assertSame($response, $actual);
    }

    #[Test]
    public function a_payment_proof_from_another_order_is_rejected_before_storage_access(): void
    {
        $order = new Order();
        $order->setRawAttributes(['id' => 55, 'payment_proof_file_id' => 10], true);
        $file = new File();
        $file->setRawAttributes(['id' => 11], true);

        $this->expectException(NotFoundHttpException::class);

        (new OrderPaymentProofController())->show($order, $file);
    }

    #[Test]
    public function private_media_never_exposes_a_public_path_or_srcset(): void
    {
        $file = new File();
        $file->setRawAttributes([
            'id' => 99,
            'disk' => 'private',
            'path' => 'media/manual-booking-receipts/private.jpg',
        ], true);

        $this->assertNull($file->path);
        $this->assertSame('', $file->srcset);
    }

    private function bindFilesystem(FilesystemAdapter $disk): void
    {
        $factory = $this->createMock(FilesystemFactory::class);
        $factory->method('disk')->with('private')->willReturn($disk);

        $this->application->instance('filesystem', $factory);
        Facade::setFacadeApplication($this->application);
        Facade::clearResolvedInstance('filesystem');
    }
}
