<?php

namespace Tests\Unit\TreatmentReservation;

use Illuminate\Support\Carbon;
use Modules\TreatmentReservation\Entities\BeauticianCalendarToken;
use Modules\TreatmentReservation\Support\CalendarTokenVerifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CalendarTokenVerifierTest extends TestCase
{
    #[Test]
    public function it_requires_a_matching_unexpired_and_unrevoked_token(): void
    {
        $now = Carbon::parse('2026-07-29 12:00:00');
        $record = new BeauticianCalendarToken();
        $record->setRawAttributes([
            'token_hash' => hash('sha256', 'secret-token'),
            'expires_at' => $now->copy()->addHour()->toDateTimeString(),
            'revoked_at' => null,
        ], true);
        $verifier = new CalendarTokenVerifier();

        $this->assertTrue($verifier->matches($record, 'secret-token', $now));
        $this->assertFalse($verifier->matches($record, 'wrong-token', $now));

        $record->setRawAttributes(array_merge($record->getAttributes(), [
            'expires_at' => $now->copy()->toDateTimeString(),
        ]), true);
        $this->assertFalse($verifier->matches($record, 'secret-token', $now));

        $record->setRawAttributes(array_merge($record->getAttributes(), [
            'expires_at' => $now->copy()->addHour()->toDateTimeString(),
            'revoked_at' => $now->copy()->subMinute()->toDateTimeString(),
        ]), true);
        $this->assertFalse($verifier->matches($record, 'secret-token', $now));
    }
}
