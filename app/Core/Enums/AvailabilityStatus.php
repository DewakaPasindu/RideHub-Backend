<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;
enum AvailabilityStatus:string
{
    use HasEnumHelpers;
    
    case AVAILABLE='available';

    case BUSY='busy';

    case OFFLINE='offline';

    case ON_TRIP='on_trip';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}