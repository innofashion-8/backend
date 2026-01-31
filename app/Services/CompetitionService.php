<?php

namespace App\Services;

use App\Models\Competition;
use Illuminate\Support\Str;

class CompetitionService
{
    protected $competition;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    public function getCompetitions()
    {
        return $this->competition->where('is_active', true)->get();
    }

    public function getCompetitionByKey(string $key)
    {
        $query = $this->competition->where('is_active', true);

        if (Str::isUuid($key)) {
            $query->where('id', $key);
        } else {
            $query->where('slug', $key);
        }

        return $query->firstOrFail();
    }
}