<?php

namespace App\Listeners;

use App\Enum\StatusRegistration;
use App\Enum\TicketCategory;
use App\Events\RegistrationStatusUpdated;
use App\Events\RegistrationSubmitted;
use App\Events\TicketCheckedIn;
use App\Jobs\SendN8nWebhookJob;

class N8nNotificationListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handleRegistrationSubmitted(RegistrationSubmitted $event): void
    {
        $url = env('N8N_WEBHOOK_URL_NOTIFICATION');
        $payload = [
            'event_type' => 'new_registration',
            'data' => [
                'user_name' => $event->registration->user->name ?? 'Unknown',
                'email' => $event->registration->user->email ?? 'Unknown',
                'event_name' => $event->type === 'event' ? ($event->registration->event->title ?? 'Event') : ($event->registration->competition->name ?? 'Competition'),
                'status' => $event->registration->status ?? 'PENDING',
                'message' => "🎉 Pendaftaran baru masuk dari {$event->registration->user->name} untuk {$event->type}!"
            ]
        ];

        dispatch(new SendN8nWebhookJob($payload, $url));
    }

    public function handleStatusUpdated(RegistrationStatusUpdated $event): void
    {
        $url = env('N8N_WEBHOOK_URL_NOTIFICATION');
        $statusStr = $event->registration->status;
        $icon = $statusStr === StatusRegistration::VERIFIED ? '✅' : ($statusStr === StatusRegistration::REJECTED ? '❌' : '⚠️');
        
        $payload = [
            'event_type' => 'status_updated',
            'data' => [
                'user_name' => $event->registration->user->name ?? 'Unknown',
                'email' => $event->registration->user->email ?? 'Unknown',
                'event_name' => $event->type === 'event' ? ($event->registration->event->title ?? 'Event') : ($event->registration->competition->name ?? 'Competition'),
                'status' => $statusStr,
                'message' => "{$icon} Status pendaftaran {$event->registration->user->name} telah diperbarui menjadi {$statusStr->value}."
            ]
        ];

        dispatch(new SendN8nWebhookJob($payload, $url));
    }

    public function handleCheckedIn(TicketCheckedIn $event): void
    {
        $url = env('N8N_WEBHOOK_URL_CHECK_IN');
        $item = $event->ticket;
        
        $eventName = $item->registration?->event?->title ?? ($item->event?->title ?? 'Event');
        
        $isSingleTicket = empty($item->ticket_code); 
        $attendedStatus = $isSingleTicket ? $item->attended_status?->value : ($item->registration?->attended_status?->value ?? 'PENDING');
        $checkInAt = $isSingleTicket ? $item->check_in_at?->setTimezone('Asia/Jakarta')->toDateTimeString() : ($item->registration?->check_in_at?->setTimezone('Asia/Jakarta')->toDateTimeString() ?? now()->setTimezone('Asia/Jakarta')->toDateTimeString());
        $userName = $isSingleTicket ? $item->user?->name : $item->registration?->user?->name ?? ($item->user?->name ?? 'Unknown');

        // Mapping Dinamis Berdasarkan Tipe Tiket
        if ($isSingleTicket) {
            $sheetData = [
                'Ticket Code'     => (string) ($item->id ?? 'Unknown'),
                'Name'            => $userName,
                'Attended Status' => $attendedStatus,
                'Check In At'     => $checkInAt,
            ];
            $ticketType = 'Single Ticket';
        } else {
            $sheetData = [
                'Ticket Code'     => $item->ticket_code ?? 'Unknown',
                'Category'        => $item->ticket_category?->value ?? TicketCategory::GUEST->value,
                'Registration By' => $userName,
                'Guest Name'      => $item->guest_name ?? 'Unknown',
                'Attended Status' => $attendedStatus,
                'Check In At'     => $checkInAt,
            ];
            $ticketType = 'Multi Ticket';
        }

        $payload = [
            'event_type'  => 'checked_in',
            'event_name'  => $eventName,
            'ticket_type' => $ticketType,
            'data'        => $sheetData
        ];

        dispatch(new SendN8nWebhookJob($payload, $url));
    }
}
