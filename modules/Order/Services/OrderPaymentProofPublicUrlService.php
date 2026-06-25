<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Meta\Support\OpenGraph;
use Modules\Order\Entities\Order;
use RuntimeException;

class OrderPaymentProofPublicUrlService
{
    /**
     * Absolute HTTPS URL on the app origin (no CDN) so WhatsApp can fetch the media.
     */
    public function whatsAppMediaUrl(File $proof, Order $order): string
    {
        $relativePath = $this->ensurePublicCopy($proof, $order);
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl === '') {
            throw new RuntimeException('Application URL is not configured.');
        }

        return OpenGraph::preferHttps($appUrl.'/storage/'.ltrim($relativePath, '/'));
    }


    private function ensurePublicCopy(File $proof, Order $order): string
    {
        $extension = strtolower((string) ($proof->extension ?: pathinfo((string) $proof->getRawOriginal('path'), PATHINFO_EXTENSION)));

        if ($extension === '') {
            $extension = 'bin';
        }

        $relativePath = sprintf(
            'order-whatsapp/%d/payment-proof-%d.%s',
            $order->id,
            $proof->id,
            $extension
        );

        $publicDisk = Storage::disk('public');

        if ($publicDisk->exists($relativePath)) {
            return $relativePath;
        }

        $sourceDiskName = $proof->disk ?: (string) config('filesystems.default', 'public');
        $sourcePath = (string) $proof->getRawOriginal('path');

        if ($sourcePath === '') {
            throw new RuntimeException('Payment proof path is missing.');
        }

        $sourceDisk = Storage::disk($sourceDiskName);

        if (! $sourceDisk->exists($sourcePath)) {
            throw new RuntimeException('Payment proof file is missing from storage.');
        }

        $publicDisk->put($relativePath, $sourceDisk->get($sourcePath));

        return $relativePath;
    }
}
