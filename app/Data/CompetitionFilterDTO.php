<?php

namespace App\Data;

class CompetitionFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $status = null,
        public readonly ?string $competitionId = null,
        public readonly int $perPage = 10,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            search: $request->input('search'),
            status: $request->input('status'),
            competitionId: $request->input('competition_id'),
            perPage: (int) $request->input('per_page', 10),
        );
    }
}