<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum VehicleType: string
{
    use HasEnumHelpers;

    case CAR = 'car';

    case VAN = 'van';

    case SUV = 'suv';

    case BUS = 'bus';

    case MOTORBIKE = 'motorbike';

    case THREE_WHEEL = 'three_wheel';

    case LORRY = 'lorry';
}
