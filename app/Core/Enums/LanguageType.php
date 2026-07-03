<?php

namespace App\Core\Enums;

use App\Core\Traits\HasEnumHelpers;

enum LanguageType: string
{
    use HasEnumHelpers;

    case ENGLISH = 'english';

    case SINHALA = 'sinhala';

    case TAMIL = 'tamil';
}