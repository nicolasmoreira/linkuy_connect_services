<?php

namespace App\Entity;

enum UserType: string
{
    case CAREGIVER = 'ROLE_CAREGIVER';
    case SENIOR = 'ROLE_SENIOR';
}
