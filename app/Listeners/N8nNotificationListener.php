<?php

namespace App\Listeners;

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

        dispatch(new SendN8nWebhookJob($payload));
    }

    public function handleStatusUpdated(RegistrationStatusUpdated $event): void
    {
        $statusStr = $event->registration->status ?? 'UNKNOWN';
        $icon = $statusStr === 'VERIFIED' ? '✅' : ($statusStr === 'REJECTED' ? '❌' : '⚠️');
        
        $payload = [
            'event_type' => 'status_updated',
            'data' => [
                'user_name' => $event->registration->user->name ?? 'Unknown',
                'email' => $event->registration->user->email ?? 'Unknown',
                'event_name' => $event->type === 'event' ? ($event->registration->event->title ?? 'Event') : ($event->registration->competition->name ?? 'Competition'),
                'status' => $statusStr,
                'message' => "{$icon} Status pendaftaran {$event->registration->user->name} telah diperbarui menjadi {$statusStr}."
            ]
        ];

        dispatch(new SendN8nWebhookJob($payload));
    }

    public function handleCheckedIn(TicketCheckedIn $event): void
    {
        $item = $event->ticket;
        
        $guestName = $item->guest_name ?? ($item->user?->name ?? 'Unknown');
        $ticketCode = $item->ticket_code ?? ($item->id ?? 'Unknown');
        
        // Gunakan nullsafe operator (?->) agar tidak error jika relasi tidak ada
        $eventName = $item->registration?->event?->title ?? ($item->event?->title ?? 'Event');

        $payload = [
            'event_type' => 'checked_in',
            'data' => [
                'guest_name' => $guestName,
                'ticket_code' => $ticketCode,
                'event_name' => $eventName,
                'message' => "🎟️ {$guestName} baru saja check-in ke venue!"
            ]
        ];

        dispatch(new SendN8nWebhookJob($payload));
    }

    public function subscribe($events): array
    {
        return [
            RegistrationSubmitted::class => 'handleRegistrationSubmitted',
            RegistrationStatusUpdated::class => 'handleStatusUpdated',
            TicketCheckedIn::class => 'handleCheckedIn',
        ];
    }
}
