<?php

namespace App\Enum;

enum UserType: string
{
    case CAREGIVER = 'ROLE_CAREGIVER';
    case SENIOR = 'ROLE_SENIOR';
}
