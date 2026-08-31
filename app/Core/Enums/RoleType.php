<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum RoleType: string
{
    use HasEnumHelpers;

    case CUSTOMER = 'customer';

    case DRIVER = 'driver';

    case VEHICLE_OWNER = 'vehicle_owner';

    case ADMIN = 'admin';

    case SUPER_ADMIN = 'super_admin';
}