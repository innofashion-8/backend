<?php

namespace App\Services\Attendance;

use App\Contracts\AttendanceManagerInterface;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Enum\AttendedStatus;
use App\DTOs\TicketDTO;
use App\Enum\TicketCategory;
use App\Events\TicketCheckedIn;
use App\Models\CompetitionMember;
use App\Models\CompetitionRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\TicketRuleService;

class MultiTicketManager implements AttendanceManagerInterface
{
    public function validateQuota(Event $event, int $requestedTickets, ?User $user = null): void
    {
        $dynamicMaxTickets = TicketRuleService::calculateMaxTickets($event, $user);
        
        if ($requestedTickets > $dynamicMaxTickets) {
            throw new Exception("You can only request up to {$dynamicMaxTickets} tickets.");
        }

        $currentTickets = EventTicket::whereHas('registration', function($query) use ($event) {
            $query->where('event_id', $event->id)
                  ->whereIn('status', ['VERIFIED', 'PENDING']);
        })->count();

        if ($event->quota !== null && $event->quota > 0 && $currentTickets + $requestedTickets > $event->quota) {
            throw new Exception("Mohon maaf, sisa kuota tiket tidak mencukupi untuk request Anda.");
        }
    }

    public function generateTickets(EventRegistration $registration, array $guestNames = []): void
    {
        $user = $registration->user;
        
        $participantUserIds = collect();
        $participantUserIds = $participantUserIds->merge(CompetitionRegistration::pluck('user_id'));
        $participantUserIds = $participantUserIds->merge(CompetitionMember::pluck('user_id'));
        $participantUserIds = $participantUserIds->unique()->toArray();
        $isParticipant = $user ? in_array($user->id, $participantUserIds) : false;

        foreach ($guestNames as $name) {
            $ticketCode = 'TIX-' . strtoupper(Str::random(8));
            
            // Ensure uniqueness
            while(EventTicket::where('ticket_code', $ticketCode)->exists()) {
                $ticketCode = 'TIX-' . strtoupper(Str::random(8));
            }

            // Fallback for new registrations
            $category = TicketCategory::GUEST->value;
            if (str_starts_with($name, 'Keluarga ')) {
                $category = TicketCategory::DFT22->value;
            } elseif ($isParticipant) {
                $category = TicketCategory::COMPETITION_PARTICIPANT->value;
            }

            EventTicket::create([
                'ticket_code' => $ticketCode,
                'event_registration_id' => $registration->id,
                'guest_name' => $name,
                'ticket_category' => $category,
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

            event(new TicketCheckedIn($ticket));
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'type' => 'TICKET',
            'item_name' => $ticket->registration->event->title,
            'user_name' => $ticket->guest_name,
            'category' => $ticket->ticket_category?->value ?? 'guest',
        ];
    }
}
