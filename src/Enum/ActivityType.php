<?php

declare(strict_types=1);

namespace App\Enum;

enum ActivityType: string
{
    case LOCATION_UPDATE = 'LOCATION_UPDATE';
    case FALL_DETECTED = 'FALL_DETECTED';
    case INACTIVITY_ALERT = 'INACTIVITY_ALERT';
    case EMERGENCY_BUTTON_PRESSED = 'EMERGENCY_BUTTON_PRESSED';
}
