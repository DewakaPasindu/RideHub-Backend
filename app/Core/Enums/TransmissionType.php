<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum TransmissionType: string
{
    use HasEnumHelpers;

    case MANUAL = 'manual';

    case AUTOMATIC = 'automatic';

    case CVT = 'cvt';

    case SEMI_AUTOMATIC = 'semi_automatic';
}
