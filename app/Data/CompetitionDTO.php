<?php

namespace App\Data;

use Carbon\Carbon;

class CompetitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $participant_type,
        public readonly ?int $min_members,
        public readonly ?int $max_members,
        public readonly string $wa_link_national,
        public readonly string $wa_link_international,
        public readonly Carbon $registration_open_at,
        public readonly Carbon $registration_close_at,
        public readonly Carbon $submission_open_at,
        public readonly Carbon $submission_close_at,
        public readonly ?string $description = null,
        public readonly bool $is_active = true,
    ) {}
}