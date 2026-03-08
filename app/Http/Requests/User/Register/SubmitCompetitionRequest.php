<?php

namespace App\Http\Requests\User\Register;

use App\Data\SubmitCompetitionDTO;
use App\Enum\CompetitionCategory;
use App\Enum\RegionType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SubmitCompetitionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('members') && is_array($this->members)) {
            // Filter: Buang data anggota yang email & namanya kosong
            $cleanedMembers = array_filter($this->members, function ($member) {
                return !empty($member['email']) && !empty($member['name']); 
            });
            
            // Re-index array (biar urutannya balik 0, 1, dst) dan timpa request asli
            $this->merge([
                'members' => array_values($cleanedMembers)
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'region' => ['required', Rule::enum(RegionType::class)],
            
            // Category wajib buat Fashion Sketch (Individu), opsional buat Restyling (Tim)
            'category' => ['nullable', Rule::enum(CompetitionCategory::class)],
            
            // Group name wajib buat Restyling (Tim)
            'group_name' => ['nullable', 'string', 'max:255'],

            // Array Members (Opsional, hanya ada kalau daftar lomba tim)
            'members' => ['nullable', 'array'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['required', 'email'],
            'members.*.phone' => ['required', 'string', 'max:20', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'members.*.id_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function toDTO(
        string $userId, 
        string $competitionId,
        array $memberFiles = [] // Nampung path file KTP anggota yang udah diupload di controller
    ): SubmitCompetitionDTO
    {
        $data = $this->validated();

        return new SubmitCompetitionDTO(
            userId: $userId,
            competitionId: $competitionId,
            region: RegionType::from($data['region']),
            category: isset($data['category']) ? CompetitionCategory::from($data['category']) : null,
            groupName: $data['group_name'] ?? null,
            membersData: $data['members'] ?? [],
            memberFiles: $memberFiles
        );
    }
}