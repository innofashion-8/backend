<?php

namespace App\Data;

use Carbon\Carbon;

class EventDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $category,
        public readonly string $description,
        public readonly int $price,
        public readonly int $quota,
        public readonly Carbon $start_time,
        public readonly bool $is_active = true,
    ) {}
}