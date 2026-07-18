<?php

namespace App\Data;

use App\Models\User;

class CompleteGuestDTO
{
    public function __construct(
        public readonly User $user,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $institution = null,
    ) {}
}
