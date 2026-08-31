<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum VehicleStatus:string
{
    use HasEnumHelpers;
    case AVAILABLE='available';

    case BOOKED='booked';

    case ON_TRIP='on_trip';

    case MAINTENANCE='maintenance';

    case INACTIVE='inactive';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}