<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum FuelType: string
{
    use HasEnumHelpers;

    case PETROL = 'petrol';

    case DIESEL = 'diesel';

    case ELECTRIC = 'electric';

    case HYBRID = 'hybrid';

    case CNG = 'cng';

    case LPG = 'lpg';
}