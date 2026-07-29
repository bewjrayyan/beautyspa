<?php

namespace Modules\TreatmentReservation\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Modules\TreatmentReservation\Entities\BeauticianCalendarToken;

final class CalendarTokenVerifier
{
    public function matches(
        BeauticianCalendarToken $record,
        string $plainToken,
        CarbonInterface $now
    ): bool {
        $revokedAt = $record->getRawOriginal('revoked_at');
        $expiresAt = $record->getRawOriginal('expires_at');

        if ($revokedAt !== null) {
            return false;
        }

        if ($expiresAt !== null && CarbonImmutable::parse($expiresAt)->lte($now)) {
            return false;
        }

        return hash_equals((string) $record->token_hash, hash('sha256', $plainToken));
    }
}
