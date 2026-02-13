<?php

namespace App\Services;

use App\Data\CompetitionDTO;
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

    public function store(CompetitionDTO $dto)
    {
        $data = [
            'name' => $dto->name,
            'slug' => Str::slug($dto->name),
            'category' => $dto->category,
            'description' => $dto->description,
            'registration_fee' => $dto->registration_fee,
            'is_active' => $dto->is_active,
        ];

        $comp = $this->competition->create($data);
        return $comp;
    }

    public function update(Competition $competition, CompetitionDTO $dto)
    {
        $dataToUpdate = [
            'name' => $dto->name,
            'category' => $dto->category,
            'description' => $dto->description,
            'registration_fee' => $dto->registration_fee,
            'is_active' => $dto->is_active,
        ];

        if ($dto->name !== $competition->name) {
            $dataToUpdate['slug'] = Str::slug($dto->name);
        }

        $competition->update($dataToUpdate);
        return $competition;
    }

    public function delete(Competition $competition)
    {
        $competition->is_active = false;
        $competition->save();
    }
}