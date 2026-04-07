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

    public function messages(): array
    {
        return [
            // Region
            'region.required' => 'Region type is required.',

            // Category
            'category.required' => 'Competition category is required.',

            // Group Name
            'group_name.string' => 'Group name must be a valid text.',
            'group_name.max' => 'Group name cannot exceed 255 characters.',

            // Members Array
            'members.array' => 'Members data must be a valid array.',

            // Member Name
            'members.*.name.required' => 'Member name is required.',
            'members.*.name.string' => 'Member name must be a valid text.',
            'members.*.name.max' => 'Member name cannot exceed 255 characters.',

            // Member Email
            'members.*.email.required' => 'Member email is required.',
            'members.*.email.email' => 'Member email must be a valid email address.',

            // Member Phone
            'members.*.phone.required' => 'Member phone number is required.',
            'members.*.phone.string' => 'Member phone number must be a valid text.',
            'members.*.phone.max' => 'Member phone number cannot exceed 20 characters.',
            'members.*.phone.regex' => 'Member phone number must be a valid Indonesian phone number (e.g., +628123456789 or 08123456789).',

            // Member ID Card
            'members.*.id_card.file' => 'Member ID card must be a valid file.',
            'members.*.id_card.mimes' => 'Member ID card must be in JPG, JPEG, PNG, or PDF format.',
            'members.*.id_card.max' => 'Member ID card file size cannot exceed 2MB.',
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