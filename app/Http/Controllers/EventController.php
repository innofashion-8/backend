<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Utils\HttpResponseCode;

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
            $event,
            HttpResponseCode::HTTP_OK
        );
    }
}