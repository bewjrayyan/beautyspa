<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderPaymentProofController
{
    public function show(Order $order, File $file): StreamedResponse
    {
        abort_unless((int) $order->payment_proof_file_id === (int) $file->id, 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($file->disk ?: 'private');
        $path = (string) $file->getRawOriginal('path');
        abort_if($path === '' || ! $disk->exists($path), 404);
        $filename = str_replace(["\r", "\n", '"'], '', basename((string) $file->filename));

        return $disk->response($path, $filename, [
            'Content-Type' => $file->mime ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
