<?php

namespace App\Data;

class SubmitEventDTO
{
    public function __construct(
        public string $userId,
        public string $eventId,
        public readonly ?string $paymentProof,
        public readonly ?array $guestNames = null,
    ) {}
}