<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'Ticket Code' => $this->ticket_code,
            'Registered By' => $this->registration->user->name,
            'Email Registered By' => $this->registration->user->email,
            'Guest Name' => $this->guest_name,
            'Ticket Category' => $this->ticket_category?->value ?? 'guest',
            'Attended Status' => $this->attended_status?->value ?? 'pending',
            'Check In At' => $this->check_in_at ? $this->check_in_at->format('Y-m-d H:i:s') : null,
            'Check Out At' => $this->check_out_at ? $this->check_out_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
