<?php

namespace App\Entity;

enum ActivityType: string
{
    case WALKING = 'WALKING';
    case RUNNING = 'RUNNING';
    case FALL_DETECTED = 'FALL_DETECTED';
    case INACTIVE = 'INACTIVE';
    const EMERGENCY_BUTTON_PRESSED = 'EMERGENCY_BUTTON_PRESSED';
}
