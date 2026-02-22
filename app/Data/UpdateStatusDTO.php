<?php

namespace App\Data;

class UpdateStatusDTO
{
    public function __construct(
        // ID dari EventRegistration atau CompetitionRegistration
        public readonly ?string $verifiedBy,
        public readonly string $registrationId,
        public readonly string $status,
        public readonly ?string $rejection_reason,
    ){}    
}