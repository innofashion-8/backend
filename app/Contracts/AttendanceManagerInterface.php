<?php

namespace App\Contracts;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Collection;

use App\Models\User;

interface AttendanceManagerInterface
{
    public function validateQuota(Event $event, int $requestedTickets, ?User $user = null): void;
    public function generateTickets(EventRegistration $registration, array $guestNames = []): void;
    public function getTickets(EventRegistration $registration): Collection;
    
    /** Process check in and return success payload details */
    public function checkIn(string $ticketCode): array;
}
