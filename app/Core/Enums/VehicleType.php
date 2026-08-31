<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum VehicleType: string
{
    use HasEnumHelpers;

    case CAR = 'car';

    case SUV = 'suv';

    case VAN = 'van';

    case MINIVAN = 'minivan';

    case BUS = 'bus';

    case TRUCK = 'truck';

    case PICKUP = 'pickup';

    case MOTORCYCLE = 'motorcycle';

    case THREE_WHEELER = 'three_wheeler';

    case LORRY = 'lorry';
}
