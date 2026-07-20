<?php

namespace App\Enum;

enum EventCategory :string
{
    case TALKSHOW = 'TALKSHOW';
    case SEMINAR = 'SEMINAR';
    case WORKSHOP = 'WORKSHOP';
    case RESTYLING = 'RESTYLING';
    case FASHION_SHOW = 'FASHION_SHOW';
}
