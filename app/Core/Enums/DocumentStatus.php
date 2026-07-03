<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum DocumentStatus:string
{
    use HasEnumHelpers;
    case PENDING='pending';

    case VERIFIED='verified';

    case REJECTED='rejected';

    case EXPIRED='expired';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}