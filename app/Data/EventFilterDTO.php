<?php

namespace App\Data;

class EventFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $status = null,
        public readonly ?string $eventId = null,
        public readonly ?string $eventName = null,
        public readonly ?string $userType = null,
        public readonly int $perPage = 10,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            status: $request->input('status'),
            eventId: $request->input('event_id'),
            eventName: $request->input('event_name'),
            userType: $request->input('user_type'),
            perPage: (int) $request->input('per_page', 10),
        );
    }
}