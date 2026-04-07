<?php

namespace App\Http\Requests\User\Register;

use App\Data\SaveDraftDTO;
use App\Http\Requests\ApiRequest;

class SaveCompetitionDraftRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ingat: Ini DRAFT, jadi SEMUA field wajib nullable (boleh kosong)
        // karena user mungkin baru ngisi setengah jalan terus disave.
        return [
            'draft_data'                           => ['nullable', 'array'],
            'draft_data.region'                    => ['nullable', 'string'],
            'draft_data.category'                  => ['nullable', 'string'],
            'draft_data.group_name'                => ['nullable', 'string', 'max:255'],
            
            // Validasi Anggota
            'draft_data.members'                   => ['nullable', 'array'],
            'draft_data.members.*.name'       => ['nullable', 'string', 'max:255'],
            'draft_data.members.*.email'           => ['nullable', 'email'],
            'draft_data.members.*.phone'  => ['nullable', 'string', 'max:20'],
            
            // Validasi File KTP Anggota (hanya dicek kalau dia nge-upload file)
            'draft_data.members.*.id_card'         => [
                'nullable', 
                'file',                 
                'mimes:jpg,jpeg,png,pdf', 
                'max:2048'              
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Draft Data
            'draft_data.array' => 'Draft data must be a valid array.',

            // Region
            'draft_data.region.string' => 'Region must be a valid text.',

            // Category
            'draft_data.category.string' => 'Category must be a valid text.',

            // Group Name
            'draft_data.group_name.string' => 'Group name must be a valid text.',
            'draft_data.group_name.max' => 'Group name cannot exceed 255 characters.',

            // Members
            'draft_data.members.array' => 'Members data must be a valid array.',
            'draft_data.members.*.name.string' => 'Member name must be a valid text.',
            'draft_data.members.*.name.max' => 'Member name cannot exceed 255 characters.',
            'draft_data.members.*.email.email' => 'Member email must be a valid email address.',
            'draft_data.members.*.phone.string' => 'Member phone must be a valid text.',
            'draft_data.members.*.phone.max' => 'Member phone cannot exceed 20 characters.',

            // Member ID Card
            'draft_data.members.*.id_card.file' => 'Member ID card must be a valid file.',
            'draft_data.members.*.id_card.mimes' => 'Member ID card must be in JPG, JPEG, PNG, or PDF format.',
            'draft_data.members.*.id_card.max' => 'Member ID card file size cannot exceed 2MB.',
        ];
    }

    public function toDTO(
        string $userId, 
        string $activityId,
        array $processedDraftData
    ): SaveDraftDTO
    {
        return new SaveDraftDTO(
            userId: $userId,
            activityId: $activityId,
            draftData: $processedDraftData,
        );
    }
}