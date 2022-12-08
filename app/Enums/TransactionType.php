<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionType extends Enum
{
    public const SENT =   1;
    public const RECEIVED =   2;
    public const WITHDRAW = 3;
    public const PAID = 4;
    public const UNKNOWN = 5;
}
