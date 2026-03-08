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