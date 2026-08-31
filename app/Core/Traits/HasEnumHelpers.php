<?php

namespace App\Core\Traits;

trait HasEnumHelpers
{
    /**
     * Return enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return enum names.
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Return enum as dropdown options.
     */
    public static function options(): array
    {
        return array_map(function ($case) {

            return [
                'label' => ucwords(str_replace('_', ' ', $case->value)),
                'value' => $case->value,
            ];

        }, self::cases());
    }

    /**
     * Check if value exists.
     */
    public static function has(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}