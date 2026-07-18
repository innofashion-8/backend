<?php

namespace App\Services\Scanner;

use App\Contracts\ScanProcessorInterface;
use App\Enum\AttendedStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketScanProcessor implements ScanProcessorInterface
{
    public function processAdminScan(string $id): array
    {
        $ticket = Ticket::with('userRsvp.rsvpSession')->find($id);

        if (!$ticket) {
            throw ValidationException::withMessages([
                'registration_id' => ['Data tiket tidak ditemukan di sistem.']
            ]);
        }

        if ($ticket->attended_status === AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['TICKET EXPIRED: Tiket ini sudah melakukan Check-In sebelumnya!']
            ]);
        }

        if ($ticket->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Tiket ini sudah melakukan Check-Out sebelumnya!']
            ]);
        }

        DB::beginTransaction();
        try {
            $ticket->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();

            return [
                'user_name' => $ticket->guest_name,
                'type'      => 'TICKET',
                'item_name' => $ticket->userRsvp->rsvpSession->title,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processUserScan(User $user, object $payload): array
    {
        // For Fashion Show / Tickets, maybe the user scans a global QR code to check-in their guests?
        // Usually ticket checking is done by Admin.
        // If we want to support User scanning to check-in themselves, we find their ticket.
        
        $ticket = Ticket::whereHas('userRsvp', function($q) use ($user, $payload) {
                $q->where('user_id', $user->id)
                  ->where('rsvp_session_id', $payload->rsvp_session_id);
            })
            ->where('attended_status', AttendedStatus::PENDING)
            ->first();
            
        if (!$ticket) {
            throw ValidationException::withMessages([
                'status' => ['ACCESS DENIED: Anda tidak memiliki tiket yang valid untuk sesi ini.']
            ]);
        }

        DB::beginTransaction();
        try {
            $ticket->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();
    
            return [
                'event_name' => $ticket->userRsvp->rsvpSession->title . ' (' . $ticket->guest_name . ')',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
