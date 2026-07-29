<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Modules\Order\Entities\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderTemporaryDocumentController
{
    public function show(Order $order, string $type, string $fingerprint): StreamedResponse
    {
        abort_unless(in_array($type, ['invoice', 'receipt'], true), 404);
        abort_unless(hash_equals(md5((string) ($order->updated_at?->timestamp ?? $order->id)), $fingerprint), 404);

        $path = "orders/{$order->id}/{$type}-{$fingerprint}.pdf";
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('private');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, "order-{$order->id}-{$type}.pdf", [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
