<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\SaveEventRequest;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use App\Utils\HttpResponseCode;
use Illuminate\Support\Facades\Crypt;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index()
    {
        $events = $this->eventService->getEvents();
        return $this->success("List Event berhasil diambil", $events, HttpResponseCode::HTTP_OK);
    }

    public function show(string $key)
    {
        $event = $this->eventService->getEventByKey($key);

        return $this->success(
            "Detail Event berhasil diambil", 
            new EventResource($event),
            HttpResponseCode::HTTP_OK
        );
    }

    public function store(SaveEventRequest $request)
    {
        $eventDTO = $request->toDTO();
        $event = $this->eventService->createEvent($eventDTO);

        return $this->success(
            "Event berhasil dibuat",
            new EventResource($event),
            HttpResponseCode::HTTP_CREATED
        );
    }

    public function update(SaveEventRequest $request, string $key)
    {
        $event = $this->eventService->getEventByKey($key);
        $eventDTO = $request->toDTO();
        $event = $this->eventService->updateEvent($event, $eventDTO);

        return $this->success(
            "Event berhasil diperbarui",
            new EventResource($event),
            HttpResponseCode::HTTP_OK
        );
    }

    public function destroy(string $key)
    {
        $event = $this->eventService->getEventByKey($key);
        $this->eventService->delete($event);

        return $this->success(
            "Event berhasil dihapus",
            null,
            HttpResponseCode::HTTP_OK
        );
    }

    public function getRotatingQr(string $key)
    {
        $event = $this->eventService->getEventByKey($key);
        $payload = json_encode([
            'event_id' => $event->id,
            'exp' => now()->addSeconds(35)->timestamp
        ]);
        $token = Crypt::encryptString($payload);
        
        return $this->success("QR Token Generated", ['token' => $token], HttpResponseCode::HTTP_OK);
    }
}