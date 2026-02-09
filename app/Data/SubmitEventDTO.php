<?php

namespace App\Data;

class SubmitEventDTO
{
    public function __construct(
        public string $userId,
        public string $eventId,
        public readonly ?string $paymentProof,
        
        public readonly ?string $nrp,
        public readonly ?int $batch,
        public readonly ?string $major,
    ) {}
}