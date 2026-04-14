<?php

namespace App\Enum;

enum EventCategory :string
{
    case TALKSHOW = 'TALKSHOW';
    case SEMINAR = 'SEMINAR';
    case WORKSHOP = 'WORKSHOP';
    case GRADUATION = 'GRADUATION';
}
