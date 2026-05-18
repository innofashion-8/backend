<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'category'    => $this->category,
            'description' => $this->description,
            'price'       => $this->price,
            'quota'       => $this->quota,
            'quota_left'  => max(0, $this->quota - $this->event_registrations_count),
            'wa_link'     => $this->wa_link,
            
            'payment_details' => $this->price > 0 ? [
                'bank_name'            => $this->bank_name,
                'bank_account_name'    => $this->bank_account_name,
                'bank_account_number'  => $this->bank_account_number,
                'transfer_note_format' => $this->transfer_note_format,
            ] : null,

            'start_time_human' => $this->start_time->translatedFormat('d F Y, H:i'),
            
            'start_time_input' => $this->start_time->format('Y-m-d\TH:i'),
            
            'start_time_iso'   => $this->start_time->toISOString(),

            'close_registration_at'       => $this->close_registration_at?->toISOString(),
            'close_registration_at_input' => $this->close_registration_at?->format('Y-m-d\TH:i'),
            'close_registration_at_human' => $this->close_registration_at?->translatedFormat('d F Y, H:i'),

            // Helper: apakah pendaftaran masih terbuka saat ini
            'is_registration_open' => !$this->close_registration_at || now()->lessThanOrEqualTo($this->close_registration_at),
            
            'is_active'   => $this->is_active,
        ];
    }
}
