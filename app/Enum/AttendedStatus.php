<?php

namespace App\Enum;

enum AttendedStatus: string
{
    case PENDING = 'pending';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
}
