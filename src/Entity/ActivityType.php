<?php

namespace App\Entity;

enum ActivityType: string
{
    case WALKING = 'walking';
    case RUNNING = 'running';
    case FALL_DETECTED = 'fall_detected';
    case INACTIVE = 'inactive';
}
