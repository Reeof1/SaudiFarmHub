<?php
declare(strict_types=1);

namespace Core;

/**
 * Fixed list of Saudi Arabia's 13 administrative regions.
 * Used to populate dropdowns for farm owners (when creating a farm)
 * and visitors (when filtering farms by city).
 */
class Cities
{
    public const LIST = [
        'Riyadh',
        'Makkah',
        'Madinah',
        'Qassim',
        'Eastern Province',
        'Asir',
        'Tabuk',
        'Hail',
        'Northern Borders',
        'Jazan',
        'Najran',
        'Baha',
        'Jouf',
    ];

    public static function isValid(string $city): bool
    {
        return in_array($city, self::LIST, true);
    }
}
