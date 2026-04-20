<?php
declare(strict_types=1);

namespace Core;

class ActivityTypes
{
    public const LIST = [
        'Entry Ticket',
        'Harvesting',
        'Animal Feeding',
        'Horse Riding',
        'Picnic',
        'BBQ / Grill',
        'Photography',
        'Workshop',
        'Camel Riding',
        'Falcon Show',
        'Bird Watching',
        'Stargazing',
        'Kids Activities',
        'Playground',
        'Beekeeping',
    ];

    public static function isValid(string $type): bool
    {
        return in_array($type, self::LIST, true);
    }
}
