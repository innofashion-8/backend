<?php

namespace App\Data;

use App\Enum\CompetitionCategory;
use App\Enum\RegionType;

class SubmitCompetitionDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $competitionId,
        public readonly RegionType $region,
        public readonly ?CompetitionCategory $category = null,
        public readonly ?string $groupName = null,
        public readonly array $membersData = [],
        public readonly array $memberFiles = [] 
    ) {}
}