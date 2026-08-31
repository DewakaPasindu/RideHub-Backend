<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum ReviewStatus:string
{
    use HasEnumHelpers;
    
    case PENDING='pending';

    case IN_PROGRESS='in_progress';

    case APPROVED='approved';

    case REJECTED='rejected';

    case MORE_INFO_REQUIRED='more_information_required';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}