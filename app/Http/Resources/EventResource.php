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
            'description' => $this->description,
            'price'       => $this->price,
            'quota'       => $this->quota,
            
            'start_time_human' => $this->start_time->translatedFormat('d F Y, H:i'),
            
            'start_time_input' => $this->start_time->format('Y-m-d\TH:i'),
            
            'start_time_iso'   => $this->start_time->toISOString(),
            
            'is_active'   => $this->is_active,
        ];
    }
}
