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
