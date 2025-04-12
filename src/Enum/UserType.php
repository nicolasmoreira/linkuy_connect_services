<?php

declare(strict_types=1);

namespace App\Enum;

enum UserType: string
{
    case CAREGIVER = 'ROLE_CAREGIVER';
    case SENIOR = 'ROLE_SENIOR';
}
