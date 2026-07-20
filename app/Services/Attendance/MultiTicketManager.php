<?php

namespace App\Services\Attendance;

use App\Contracts\AttendanceManagerInterface;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Enum\AttendedStatus;
use App\DTOs\TicketDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class MultiTicketManager implements AttendanceManagerInterface
{
    public function validateQuota(Event $event, int $requestedTickets): void
    {
        if ($requestedTickets > $event->max_tickets_per_user) {
            throw new Exception("You can only request up to {$event->max_tickets_per_user} tickets.");
        }

        $currentTickets = EventTicket::whereHas('registration', function($query) use ($event) {
            $query->where('event_id', $event->id)
                  ->whereIn('status', ['VERIFIED', 'PENDING']);
        })->count();

        if ($currentTickets + $requestedTickets > $event->quota) {
            throw new Exception("Mohon maaf, sisa kuota tiket tidak mencukupi untuk request Anda.");
        }
    }

    public function generateTickets(EventRegistration $registration, array $guestNames = []): void
    {
        foreach ($guestNames as $name) {
            $ticketCode = 'TIX-' . strtoupper(Str::random(8));
            
            // Ensure uniqueness
            while(EventTicket::where('ticket_code', $ticketCode)->exists()) {
                $ticketCode = 'TIX-' . strtoupper(Str::random(8));
            }

            EventTicket::create([
                'ticket_code' => $ticketCode,
                'event_registration_id' => $registration->id,
                'guest_name' => $name,
                'attended_status' => AttendedStatus::PENDING->value,
            ]);
        }
    }

    public function getTickets(EventRegistration $registration): Collection
    {
        return $registration->tickets->map(fn($ticket) => new TicketDTO(
            ticketCode: $ticket->ticket_code,
            guestName: $ticket->guest_name,
            attendedStatus: $ticket->attended_status?->value ?? 'PENDING',
            checkInAt: $ticket->check_in_at?->toIso8601String(),
            checkOutAt: $ticket->check_out_at?->toIso8601String()
        ));
    }

    public function checkIn(string $ticketCode): array
    {
        $ticket = EventTicket::with('registration.event')->where('ticket_code', $ticketCode)->firstOrFail();
        
        if ($ticket->attended_status === AttendedStatus::CHECKED_IN) {
            throw new Exception("Ticket already checked in.");
        }

        DB::beginTransaction();
        try {
            $ticket->update([
                'attended_status' => AttendedStatus::CHECKED_IN->value,
                'check_in_at' => now()
            ]);

            $pendingTicketsLeft = EventTicket::where('event_registration_id', $ticket->event_registration_id)
                ->where('attended_status', AttendedStatus::PENDING->value)
                ->count();

            if ($pendingTicketsLeft === 0) {
                $ticket->registration->update([
                    'attended_status' => AttendedStatus::CHECKED_IN->value,
                    'check_in_at' => now()
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'type' => 'TICKET',
            'item_name' => $ticket->registration->event->title,
            'user_name' => $ticket->guest_name,
        ];
    }
}
