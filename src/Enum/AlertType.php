<?php

namespace App\Enum;

enum AlertType: string
{
    case FALL = 'fall';
    case INACTIVITY = 'inactivity';
    case EMERGENCY = 'emergency';
}
