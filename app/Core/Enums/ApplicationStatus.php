<?php

namespace App\Core\Enums;
use App\Core\Traits\HasEnumHelpers;

enum ApplicationStatus: string
{
    use HasEnumHelpers;
    
    case DRAFT = 'draft';

    case PENDING = 'pending';

    case UNDER_REVIEW = 'under_review';

    case MORE_INFORMATION_REQUIRED = 'more_info_required';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case WITHDRAWN = 'withdrawn';

    case SUSPENDED = 'suspended';

    case ARCHIVED = 'archived';

    /**
     * Return all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return options for dropdowns or APIs.
     */
    public static function options(): array
    {
        return array_map(fn ($case) => [
            'label' => ucwords(str_replace('_', ' ', $case->value)),
            'value' => $case->value,
        ], self::cases());
    }
}