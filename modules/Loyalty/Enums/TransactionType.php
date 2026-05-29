<?php

namespace Modules\Loyalty\Enums;

final class TransactionType
{
    public const EARN = 'earn';

    public const REDEEM = 'redeem';

    public const ADJUST = 'adjust';

    public const EXPIRE = 'expire';

    public const CLAWBACK = 'clawback';

    public const BONUS = 'bonus';
}
