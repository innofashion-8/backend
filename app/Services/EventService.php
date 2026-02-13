<?php

namespace App\Services;

use App\Data\EventDTO;
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

    public function createEvent(EventDTO $dto)
    {
        $event = $this->event->create([
            'title'       => $dto->title,
            'slug'        => Str::slug($dto->title) . '-' . Str::random(6),
            'category'    => $dto->category,
            'description' => $dto->description,
            'price'       => $dto->price,
            'quota'       => $dto->quota,
            'start_time'  => $dto->start_time,
            'is_active'   => $dto->is_active,
        ]);
        return $event;
    }

    public function updateEvent(Event $event, EventDTO $dto)
    {
        $dataToUpdate = [
            'title'       => $dto->title,
            'category'    => $dto->category,
            'description' => $dto->description,
            'price'       => $dto->price,
            'quota'       => $dto->quota,
            'start_time'  => $dto->start_time,
            'is_active'   => $dto->is_active,
        ];

        if ($dto->title !== $event->title) {
            $dataToUpdate['slug'] = Str::slug($dto->title) . '-' . Str::random(6);
        }

        $event->update($dataToUpdate);

        return $event;
    }

    public function delete(Event $event)
    {
        $event->is_active = false;
        $event->save();
    }
}