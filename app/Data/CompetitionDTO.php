<?php

namespace App\Data;

class CompetitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $category,
        public readonly ?string $description,
        public readonly int $registration_fee,
        public readonly bool $is_active = true,
    ) {}
}