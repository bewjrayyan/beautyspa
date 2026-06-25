<?php

namespace Modules\Order\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Media\Services\ImageOptimizationService;
use Modules\Order\Entities\Order;

class OrderPaymentProofService
{
    public function storeForOrder(Order $order, ?UploadedFile $file): ?int
    {
        if (! $file) {
            return null;
        }

        $diskName = 'public_storage';
        $disk = Storage::disk($diskName);
        $path = $disk->putFile('media/order-payment-proofs', $file);

        if ($path === false) {
            throw new \RuntimeException('Failed to store payment proof.');
        }

        $optimized = app(ImageOptimizationService::class)->processUploadedFile($file, $path, $diskName);
        $userId = $order->customer_id ?? auth()->id() ?? 1;

        return File::create([
            'user_id' => $userId,
            'disk' => $diskName,
            'filename' => substr($file->getClientOriginalName(), 0, 255),
            'path' => $optimized['path'],
            'extension' => $optimized['extension'],
            'mime' => $optimized['mime'],
            'size' => $optimized['size'],
            'responsive_paths' => $optimized['responsive_paths'] ?: null,
        ])->id;
    }
}
