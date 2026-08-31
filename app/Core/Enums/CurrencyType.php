<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum CurrencyType: string
{
    use HasEnumHelpers;

    case LKR = 'LKR';

    case USD = 'USD';

    case EUR = 'EUR';

    case GBP = 'GBP';

    case INR = 'INR';
}