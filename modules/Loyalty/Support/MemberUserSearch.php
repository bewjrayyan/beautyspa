<?php

namespace Modules\Loyalty\Support;

use Illuminate\Database\Eloquent\Builder;
use Modules\User\Support\PhoneNumber;

class MemberUserSearch
{
    public static function apply(Builder $userQuery, string $keyword): void
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return;
        }

        $like = '%' . $keyword . '%';
        $normalizedIdentity = strtoupper(preg_replace('/\s+/', '', $keyword));
        $normalizedPhone = PhoneNumber::normalize($keyword);
        $digitsOnly = preg_replace('/\D+/', '', $keyword);

        $userQuery->where(function ($q) use ($like, $normalizedIdentity, $normalizedPhone, $digitsOnly) {
            $q->where('email', 'like', $like)
                ->orWhere('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhereRaw(
                    "TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?",
                    [$like]
                );

            if ($normalizedIdentity !== '') {
                $q->orWhere('identity_number', 'like', '%' . $normalizedIdentity . '%');
            }

            if ($normalizedPhone !== '') {
                $q->orWhereIn('phone', PhoneNumber::variants($normalizedPhone));
            }

            if (strlen($digitsOnly) >= 3) {
                $q->orWhere('phone', 'like', '%' . $digitsOnly . '%');
            }
        });
    }
}
