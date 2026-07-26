<?php

namespace Modules\Account\Services;

use Modules\Account\Entities\ConsultationSubmission;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class ConsultationCustomerAccess
{
    public function canAccess(ConsultationSubmission $submission, User $user): bool
    {
        if ($submission->revoked_at !== null) {
            return false;
        }

        if ($submission->user_id !== null) {
            return (int) $submission->user_id === (int) $user->id;
        }

        return $this->emailMatches($submission, (string) $user->email)
            || $this->phoneMatches($submission, (string) $user->phone);
    }

    public function identifierMatches(
        ConsultationSubmission $submission,
        string $identifier,
        bool $isEmail
    ): bool {
        if ($isEmail) {
            $accountEmail = $submission->user_id !== null
                ? (string) $submission->user()->value('email')
                : '';

            return $this->emailMatches($submission, $identifier)
                || $this->sameEmail($identifier, $accountEmail);
        }

        $accountPhone = $submission->user_id !== null
            ? (string) $submission->user()->value('phone')
            : '';

        return $this->phoneMatches($submission, $identifier)
            || $this->samePhone($identifier, $accountPhone);
    }

    public function claim(ConsultationSubmission $submission, User $user): void
    {
        abort_unless($this->canAccess($submission, $user), 403);

        if ($submission->user_id !== null) {
            return;
        }

        ConsultationSubmission::query()
            ->whereKey($submission->id)
            ->whereNull('user_id')
            ->whereNull('revoked_at')
            ->update([
                'user_id' => $user->id,
                'updated_at' => now(),
            ]);

        $submission->refresh();
        abort_unless((int) $submission->user_id === (int) $user->id, 403);
    }

    private function emailMatches(ConsultationSubmission $submission, string $email): bool
    {
        return filled($submission->customer_email)
            && $this->sameEmail($email, (string) $submission->customer_email);
    }

    private function phoneMatches(ConsultationSubmission $submission, string $phone): bool
    {
        return filled($submission->customer_phone)
            && $this->samePhone($phone, (string) $submission->customer_phone);
    }

    private function sameEmail(string $left, string $right): bool
    {
        return $left !== ''
            && $right !== ''
            && mb_strtolower(trim($left)) === mb_strtolower(trim($right));
    }

    private function samePhone(string $left, string $right): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        return in_array(
            PhoneNumber::normalize($left),
            PhoneNumber::variants($right),
            true
        );
    }
}
