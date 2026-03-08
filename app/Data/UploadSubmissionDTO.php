<?php

namespace App\Data;

class UploadSubmissionDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $competitionId,
        public readonly string $artworkPath,
        public readonly string $conceptPath,
    ) {}
}