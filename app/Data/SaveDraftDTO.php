<?php

namespace App\Data;

class SaveDraftDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $activityId,
        public readonly array $draftData
    )
    {}
}