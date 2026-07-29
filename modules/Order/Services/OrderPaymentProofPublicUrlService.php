<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\URL;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

class OrderPaymentProofPublicUrlService
{
    public function whatsAppMediaUrl(File $proof, Order $order): string
    {
        abort_unless((int) $order->payment_proof_file_id === (int) $proof->id, 404);

        return URL::temporarySignedRoute(
            'order.payment_proofs.temporary',
            now()->addMinutes(90),
            ['order' => $order->id, 'file' => $proof->id]
        );
    }
}
