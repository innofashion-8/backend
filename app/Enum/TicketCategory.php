<?php

namespace App\Enum;

enum TicketCategory: string
{
    case GUEST = 'guest';
    case DFT22 = 'dft22';
    case COMPETITION_PARTICIPANT = 'competition_participant';
}
