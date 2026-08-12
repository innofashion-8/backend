<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;
use App\Models\EventTicket;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceLogController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->query('search');
        $userType = $request->query('user_type');
        $eventId  = $request->query('event_id');
        $perPage  = (int) $request->query('per_page', 15);

        // 1. Fetch checked in tickets
        $ticketLogsQuery = EventTicket::with(['registration.user', 'registration.event'])
            ->whereNotNull('check_in_at');

        if ($search) {
            $ticketLogsQuery->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('ticket_code', 'like', "%{$search}%")
                  ->orWhereHas('registration.user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($userType && $userType !== 'ALL') {
            $ticketLogsQuery->whereHas('registration.user', function ($u) use ($userType) {
                $u->where('type', $userType);
            });
        }

        if ($eventId) {
            $ticketLogsQuery->whereHas('registration', function ($r) use ($eventId) {
                $r->where('event_id', $eventId);
            });
        }

        $ticketLogs = $ticketLogsQuery->get()->map(function ($ticket) {
            $reg  = $ticket->registration;
            $user = $reg ? $reg->user : null;
            $evt  = $reg ? $reg->event : null;

            $checkInTime = $ticket->check_in_at ? Carbon::parse($ticket->check_in_at) : null;

            return [
                'id'          => $ticket->id,
                'source_type' => 'ticket',
                'user_name'   => $ticket->guest_name ?: ($user ? $user->name : 'N/A'),
                'user_type'   => $user ? (is_object($user->type) ? $user->type->value : $user->type) : 'GUEST',
                'email'       => $user ? $user->email : null,
                'institution' => $user ? $user->institution : 'General Public',
                'ticket_code' => $ticket->ticket_code,
                'event_title' => $evt ? $evt->title : 'N/A',
                'check_in_at' => $checkInTime ? $checkInTime->toIso8601String() : null,
                'scan_date'   => $checkInTime ? $checkInTime->format('d M Y') : '-',
                'scan_time'   => $checkInTime ? $checkInTime->format('H:i:s') : '-',
            ];
        });

        // 2. Fetch checked in event registrations without tickets (single event registrations)
        $regLogsQuery = EventRegistration::with(['user', 'event'])
            ->whereNotNull('check_in_at')
            ->doesntHave('tickets');

        if ($search) {
            $regLogsQuery->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($userType && $userType !== 'ALL') {
            $regLogsQuery->whereHas('user', function ($u) use ($userType) {
                $u->where('type', $userType);
            });
        }

        if ($eventId) {
            $regLogsQuery->where('event_id', $eventId);
        }

        $regLogs = $regLogsQuery->get()->map(function ($reg) {
            $user = $reg->user;
            $evt  = $reg->event;
            $checkInTime = $reg->check_in_at ? Carbon::parse($reg->check_in_at) : null;

            return [
                'id'          => $reg->id,
                'source_type' => 'registration',
                'user_name'   => $user ? $user->name : 'N/A',
                'user_type'   => $user ? (is_object($user->type) ? $user->type->value : $user->type) : 'EXTERNAL',
                'email'       => $user ? $user->email : null,
                'institution' => $user ? $user->institution : '-',
                'ticket_code' => 'REG-' . strtoupper(substr($reg->id, 0, 8)),
                'event_title' => $evt ? $evt->title : 'N/A',
                'check_in_at' => $checkInTime ? $checkInTime->toIso8601String() : null,
                'scan_date'   => $checkInTime ? $checkInTime->format('d M Y') : '-',
                'scan_time'   => $checkInTime ? $checkInTime->format('H:i:s') : '-',
            ];
        });

        // 3. Merge and sort by check_in_at DESC
        $mergedLogs = $ticketLogs->concat($regLogs)
            ->sortByDesc('check_in_at')
            ->values();

        // Calculate statistics
        $stats = [
            'total_scanned' => $mergedLogs->count(),
            'internal'      => $mergedLogs->where('user_type', 'INTERNAL')->count(),
            'external'      => $mergedLogs->where('user_type', 'EXTERNAL')->count(),
            'guest'         => $mergedLogs->where('user_type', 'GUEST')->count(),
        ];

        // Manual pagination
        $currentPage = (int) $request->query('page', 1);
        $total = $mergedLogs->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $mergedLogs->slice($offset, $perPage)->values();

        return $this->success('Attendance logs fetched successfully', [
            'data' => $items,
            'meta' => [
                'current_page' => $currentPage,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => ceil($total / $perPage) ?: 1,
            ],
            'stats' => $stats,
        ]);
    }
}
