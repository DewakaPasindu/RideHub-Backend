<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum GenderType: string
{
    use HasEnumHelpers;

    case MALE = 'male';

    case FEMALE = 'female';

    case OTHER = 'other';

    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';
}