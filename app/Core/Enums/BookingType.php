<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum BookingType: string
{
    use HasEnumHelpers;

    case SELF_DRIVE = 'self_drive';

    case WITH_DRIVER = 'with_driver';

    case DRIVER_ONLY = 'driver_only';
}