<?php

namespace App\Services;

use App\Data\CompetitionDTO;
use App\Enum\ParticipantType;
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
        $minMembers = $dto->participant_type === ParticipantType::INDIVIDUAL->value ? 1 : $dto->min_members;
        $maxMembers = $dto->participant_type === ParticipantType::INDIVIDUAL->value ? 1 : $dto->max_members;

        $data = [
            'name'                  => $dto->name,
            'slug'                  => Str::slug($dto->name),
            'participant_type'      => $dto->participant_type,
            'min_members'           => $minMembers,
            'max_members'           => $maxMembers,
            'wa_link_national'      => $dto->wa_link_national,
            'wa_link_international' => $dto->wa_link_international,
            'description'           => $dto->description,
            'is_active'             => $dto->is_active,
            'registration_open_at'  => $dto->registration_open_at,
            'registration_close_at' => $dto->registration_close_at,
            'submission_open_at'    => $dto->submission_open_at,
            'submission_close_at'   => $dto->submission_close_at,
        ];

        return $this->competition->create($data);
    }

    public function update(Competition $competition, CompetitionDTO $dto)
    {
        $minMembers = $dto->participant_type === ParticipantType::INDIVIDUAL->value ? 1 : $dto->min_members;
        $maxMembers = $dto->participant_type === ParticipantType::INDIVIDUAL->value ? 1 : $dto->max_members;

        $dataToUpdate = [
            'name'                  => $dto->name,
            'participant_type'      => $dto->participant_type,
            'min_members'           => $minMembers,
            'max_members'           => $maxMembers,
            'wa_link_national'      => $dto->wa_link_national,
            'wa_link_international' => $dto->wa_link_international,
            'description'           => $dto->description,
            'is_active'             => $dto->is_active,
            'registration_open_at'  => $dto->registration_open_at,
            'registration_close_at' => $dto->registration_close_at,
            'submission_open_at'    => $dto->submission_open_at,
            'submission_close_at'   => $dto->submission_close_at,
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