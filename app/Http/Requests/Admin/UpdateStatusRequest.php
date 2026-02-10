<?php

namespace App\Http\Requests\Admin;

use App\Data\UpdateStatusDTO;
use App\Enum\StatusRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in([
                    StatusRegistration::VERIFIED->value, 
                    StatusRegistration::REJECTED->value,
                    StatusRegistration::PENDING->value
                ]),
            ],
            'rejection_reason' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::requiredIf($this->status === StatusRegistration::REJECTED->value)
            ]
        ];
    }

    public function toDTO($id)
    {
        $data = $this->validated();
        return new UpdateStatusDTO(
            registrationId: $id,
            status: $data['status'],
            rejection_reason: $data['rejection_reason'] ?? null
        );
    }
}
