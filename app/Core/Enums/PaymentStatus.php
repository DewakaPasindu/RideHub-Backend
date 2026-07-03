<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum PaymentStatus:string
{
    use HasEnumHelpers;
    
    case PENDING='pending';

    case PAID='paid';

    case FAILED='failed';

    case REFUNDED='refunded';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}