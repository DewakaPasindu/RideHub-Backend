<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum OwnerType: string
{
    use HasEnumHelpers;

    case INDIVIDUAL = 'individual';

    case BUSINESS = 'business';
}