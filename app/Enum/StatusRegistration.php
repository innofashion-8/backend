<?php

namespace App\Enum;

enum StatusRegistration: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';
}
