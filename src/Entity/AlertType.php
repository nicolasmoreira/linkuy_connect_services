<?php

namespace App\Entity;

enum AlertType: string
{
    case FALL = 'fall';
    case INACTIVITY = 'inactivity';
    case EMERGENCY = 'emergency';
}
