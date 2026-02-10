<?php

namespace App\Data;

class EventFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $status = null,
        public readonly ?string $eventId = null,
        public readonly int $perPage = 10,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            status: $request->input('status'),
            eventId: $request->input('event_id'),
            perPage: (int) $request->input('per_page', 10),
        );
    }
}