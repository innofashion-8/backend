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

    public function messages(): array
    {
        return [
            // Status
            'status.required' => 'Registration status is required.',
            'status.in' => 'Invalid registration status. Must be VERIFIED, REJECTED, or PENDING.',

            // Rejection Reason
            'rejection_reason.string' => 'Rejection reason must be a valid text.',
            'rejection_reason.max' => 'Rejection reason cannot exceed 255 characters.',
            'rejection_reason.required_if' => 'Rejection reason is required when status is REJECTED.',
        ];
    }

    public function toDTO($id)
    {
        $data = $this->validated();
        return new UpdateStatusDTO(
            verifiedBy: $this->user()->id,
            registrationId: $id,
            status: $data['status'],
            rejection_reason: $data['rejection_reason'] ?? null
        );
    }
}
