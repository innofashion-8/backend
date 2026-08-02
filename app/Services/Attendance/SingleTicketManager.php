<?php

namespace App\Services\Attendance;

use App\Contracts\AttendanceManagerInterface;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Enum\AttendedStatus;
use App\DTOs\TicketDTO;
use Illuminate\Support\Collection;
use Exception;
use App\Models\User;

class SingleTicketManager implements AttendanceManagerInterface
{
    public function validateQuota(Event $event, int $requestedTickets, ?User $user = null): void
    {
        if ($requestedTickets > 1) {
            throw new Exception("This event only allows 1 ticket per registration.");
        }

        $currentRegistrations = EventRegistration::where('event_id', $event->id)
            ->whereIn('status', ['VERIFIED', 'PENDING'])
            ->count();
            
        if ($event->quota !== null && $event->quota > 0 && $currentRegistrations >= $event->quota) {
            throw new Exception("Mohon maaf, kuota untuk event ini sudah penuh.");
        }
    }

    public function generateTickets(EventRegistration $registration, array $guestNames = []): void
    {
        // No separate tickets needed for standard events, handled by the EventRegistration model itself.
    }

    public function getTickets(EventRegistration $registration): Collection
    {
        return collect([
            new TicketDTO(
                ticketCode: $registration->id,
                guestName: $registration->user->name,
                attendedStatus: $registration->attended_status?->value ?? 'PENDING',
                checkInAt: $registration->check_in_at?->toIso8601String(),
                checkOutAt: $registration->check_out_at?->toIso8601String()
            )
        ]);
    }

    public function checkIn(string $ticketCode): array
    {
        $reg = EventRegistration::findOrFail($ticketCode);
        
        if ($reg->attended_status === AttendedStatus::CHECKED_IN) {
            throw new Exception("Ticket already checked in.");
        }

        $reg->update([
            'attended_status' => AttendedStatus::CHECKED_IN->value,
            'check_in_at' => now()
        ]);

        return [
            'type' => 'EVENT',
            'item_name' => $reg->event->title,
            'user_name' => $reg->user->name,
        ];
    }
}
