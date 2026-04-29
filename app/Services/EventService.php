<?php

namespace App\Services;

use App\Data\EventDTO;
use App\Enum\StatusRegistration;
use App\Http\Resources\EventResource;
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
        $query = $this->event->where('is_active', true)
                            ->withCount(['eventRegistrations' => function ($query) {
                                $query->where('status', '!=', StatusRegistration::DRAFT);
                            }]);

        if (Str::isUuid($key)) {
            $query->where('id', $key);
        } else {
            $query->where('slug', $key);
        }

        return $query->firstOrFail();
    }

    public function getEvents()
    {
        $events = $this->event->where('is_active', true)
                    ->withCount(['eventRegistrations' => function ($query) {
                        $query->where('status', '!=', StatusRegistration::DRAFT);
                    }])
                    ->orderBy('start_time', 'asc')
                    ->get();
        return EventResource::collection($events);
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
            'wa_link'     => $dto->wa_link,
            'start_time'  => $dto->start_time,
            'is_active'   => $dto->is_active,
        ]);
        
        $event->loadCount(['eventRegistrations' => function ($query) {
            $query->where('status', '!=', StatusRegistration::DRAFT);
        }]);
        
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
            'wa_link'     => $dto->wa_link,
            'start_time'  => $dto->start_time,
            'is_active'   => $dto->is_active,
        ];

        if ($dto->title !== $event->title) {
            $dataToUpdate['slug'] = Str::slug($dto->title) . '-' . Str::random(6);
        }

        $event->update($dataToUpdate);
        
        $event->loadCount(['eventRegistrations' => function ($query) {
            $query->where('status', '!=', StatusRegistration::DRAFT);
        }]);

        return $event;
    }

    public function delete(Event $event)
    {
        $event->is_active = false;
        $event->save();
    }
}