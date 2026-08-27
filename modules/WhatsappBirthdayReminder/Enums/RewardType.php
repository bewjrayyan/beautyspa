<?php

namespace Modules\WhatsappBirthdayReminder\Enums;

final class RewardType
{
    public const POINTS = 'points';

    public const DISCOUNT = 'discount';

    public const VOUCHER = 'voucher';

    public const NONE = 'none';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::POINTS,
            self::DISCOUNT,
            self::VOUCHER,
            self::NONE,
        ];
    }
}
