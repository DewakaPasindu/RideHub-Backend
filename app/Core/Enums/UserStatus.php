<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum UserStatus:string
{
    use HasEnumHelpers;
    
    case ACTIVE='active';

    case INACTIVE='inactive';

    case SUSPENDED='suspended';

    case BLOCKED='blocked';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}