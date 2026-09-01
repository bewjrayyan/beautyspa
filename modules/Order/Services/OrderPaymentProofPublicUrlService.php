<?php

namespace Modules\Order\Services;

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

        // Relative signatures must exclude the install base (see aestheticcart_subdirectory_safe_temporary_signed_route).
        $relative = aestheticcart_subdirectory_safe_temporary_signed_route(
            'order.payment_proofs.temporary',
            now()->addMinutes(90),
            ['order' => $order->id, 'file' => $proof->id]
        );

        return $this->absoluteFromRelative($relative);
    }


    private function absoluteFromRelative(string $relative): string
    {
        return aestheticcart_absolute_from_relative_path($relative);
    }
}
