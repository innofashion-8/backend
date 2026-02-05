<?php

namespace App\Http\Requests\User\Register;

use App\Data\SaveDraftDTO;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class SaveDraftRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'draft_data' => 'required|array',
            'draft_data.payment_proof' => [
                'nullable', 
                'file',                 
                'mimes:jpg,jpeg,png,pdf', 
                'max:2048'              
            ],

            // Validasi File: KTM
            'draft_data.ktm_path' => [
                'nullable', 
                'file', 
                'mimes:jpg,jpeg,png',
                'max:2048'
            ],

            'draft_data.id_card_path' => [
                'nullable', 
                'file', 
                'mimes:jpg,jpeg,png', 
                'max:2048'
            ],
            'draft_data.nrp'       => 'nullable|string|max:50',
            'draft_data.major'     => 'nullable|string|max:100',
            'draft_data.batch'     => 'nullable|integer|min:2018|max:' . date('Y'),
        ];
    }

    public function toDTO(string $userId, string $activityId): SaveDraftDTO
    {
        $data = $this->validated();
        return new SaveDraftDTO(
            userId: $userId,
            activityId: $activityId,
            draftData: $data['draft_data'],
        );
    }
}
