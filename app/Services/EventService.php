<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;

class EventService
{
    protected $event;
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
    public function getEventByKey(string $key)
    {
        $query = $this->event->where('is_active', true);

        if (Str::isUuid($key)) {
            $query->where('id', $key);
        } else {
            $query->where('slug', $key);
        }

        return $query->firstOrFail();
    }

    public function getEvents()
    {
        return $this->event->where('is_active', true)
                    ->orderBy('start_time', 'asc')
                    ->get();
    }
}