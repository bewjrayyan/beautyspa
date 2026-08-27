<?php

namespace Modules\Order\Services;

use AestheticCart\Http\FixSubdirectoryRequest;
use Illuminate\Support\Facades\URL;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

/**
 * Builds expiring public URLs for private payment-proof files (admin preview + WhatsApp).
 */
class OrderPaymentProofPublicUrlService
{
    public function whatsAppMediaUrl(File $proof, Order $order): string
    {
        abort_unless((int) $order->payment_proof_file_id === (int) $proof->id, 404);

        // Relative signatures survive FixSubdirectoryRequest (strips /fleetcart from REQUEST_URI).
        $relative = URL::temporarySignedRoute(
            'order.payment_proofs.temporary',
            now()->addMinutes(90),
            ['order' => $order->id, 'file' => $proof->id],
            absolute: false
        );

        return $this->absoluteFromRelative($relative);
    }


    private function absoluteFromRelative(string $relative): string
    {
        $root = rtrim((string) (FixSubdirectoryRequest::resolvedAppUrl() ?: config('app.url')), '/');

        return $root.$relative;
    }
}
