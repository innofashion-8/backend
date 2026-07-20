<?php

namespace App\DTOs;

class TicketDTO
{
    public function __construct(
        public string $ticketCode,
        public string $guestName,
        public string $attendedStatus,
        public ?string $checkInAt,
        public ?string $checkOutAt
    ) {}
}
