<?php

namespace App\Data;

class SubmitCompetitionDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $competitionId,
        public readonly ?string $paymentProof,
        
        public readonly ?string $nrp,
        public readonly ?int $batch,
        public readonly ?string $major,
        public readonly ?string $ktmPath,
        public readonly ?string $idCardPath,
        
    ) {}
}