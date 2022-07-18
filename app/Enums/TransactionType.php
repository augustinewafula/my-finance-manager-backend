<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionType extends Enum
{
    const SENT =   1;
    const RECEIVED =   2;
    const WITHDRAW = 3;
}
