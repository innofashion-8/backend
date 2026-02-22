<?php

namespace App\Data;

class ProfileDraftDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly array $draftData
    )
    {}
}