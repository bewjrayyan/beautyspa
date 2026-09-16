<?php

namespace Modules\TreatmentReservation\Services;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class BeauticianWhatsAppRecipientResolver
{
    /**
     * @return list<string>
     */
    public function resolve(TreatmentBooking $booking): array
    {
        $booking->loadMissing('beautician.user');

        $phone = PhoneNumber::normalize((string) (
            $booking->beautician?->phone ?: $booking->beautician?->user?->phone
        ));

        if (PhoneNumber::isDeliverableWhatsAppRecipient($phone)) {
            return [$phone];
        }

        return app(OneSenderWhatsAppService::class)->configuredAdminPhones();
    }
}
