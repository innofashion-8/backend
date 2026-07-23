<?php

namespace App\Services\Attendance;

use App\Contracts\AttendanceManagerInterface;
use App\Enum\EventCategory;
use App\Models\Event;
use Illuminate\Support\Str;

class AttendanceManagerFactory
{
    public static function makeForEvent(Event $event): AttendanceManagerInterface
    {
        if ($event->max_tickets_per_user > 1 || $event->category === EventCategory::FASHION_SHOW) {
            return new MultiTicketManager();
        }
        
        return new SingleTicketManager();
    }

    public static function makeFromTicketCode(string $ticketCode): AttendanceManagerInterface
    {
        if (Str::startsWith($ticketCode, 'TIX-')) {
            return new MultiTicketManager();
        }
        return new SingleTicketManager();
    }
}
