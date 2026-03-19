<?php

namespace App\Http\Requests\User;

use App\Data\UpdateProfileDTO;
use App\Enum\UserType;
use App\Http\Requests\ApiRequest;

class UpdateProfileRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isInternal = $user->type === UserType::INTERNAL;

        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:100'],
            'major'       => ['required', 'string', 'max:100'],
            'line'        => ['nullable', 'string', 'regex:/^[a-zA-Z0-9._-]{4,20}$/'],
            
            'phone'       => [
                'required', 
                'string', 
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/',
                'unique:users,phone,' . $user->id 
            ],
        ];

        if ($isInternal) {
            $rules['nrp']      = ['required', 'string', 'max:50'];
            $rules['batch']    = ['required', 'string', 'max:4'];
            $rules['ktm_path'] = ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'];
        } else {
            $rules['id_card_path'] = ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Full name is required.',
            'name.string' => 'Full name must be a valid text.',
            'name.max' => 'Full name cannot exceed 255 characters.',

            // Phone
            'phone.required' => 'WhatsApp number is required.',
            'phone.string' => 'WhatsApp number must be a valid text.',
            'phone.regex' => 'WhatsApp number must be a valid Indonesian phone number (e.g., +628123456789 or 08123456789).',
            'phone.unique' => 'This WhatsApp number has already been registered by another user.',

            // Institution
            'institution.required' => 'Institution/School name is required.',
            'institution.string' => 'Institution name must be a valid text.',
            'institution.max' => 'Institution name cannot exceed 100 characters.',

            // Major
            'major.required' => 'Major/Department is required.',
            'major.string' => 'Major/Department must be a valid text.',
            'major.max' => 'Major/Department cannot exceed 100 characters.',

            // Line
            'line.string' => 'LINE ID must be a valid text.',
            'line.regex' => 'LINE ID must be 4-20 characters and can only contain letters, numbers, dots, underscores, and hyphens.',

            // NRP (Internal)
            'nrp.required' => 'NRP (Student ID) is required for internal users.',
            'nrp.string' => 'NRP must be a valid text.',
            'nrp.max' => 'NRP cannot exceed 50 characters.',

            // Batch (Internal)
            'batch.required' => 'Batch/Year is required for internal users.',
            'batch.string' => 'Batch/Year must be a valid text.',
            'batch.max' => 'Batch/Year cannot exceed 4 characters.',

            // KTM Path (Internal)
            'ktm_path.file' => 'Student ID Card must be a valid file.',
            'ktm_path.mimes' => 'Student ID Card must be in JPEG, PNG, JPG, or PDF format.',
            'ktm_path.max' => 'Student ID Card file size cannot exceed 2MB.',

            // ID Card Path (External)
            'id_card_path.file' => 'Identity Card must be a valid file.',
            'id_card_path.mimes' => 'Identity Card must be in JPEG, PNG, JPG, or PDF format.',
            'id_card_path.max' => 'Identity Card file size cannot exceed 2MB.',
        ];
    }

    public function toDTO(string $userId): UpdateProfileDTO
    {
        return new UpdateProfileDTO(
            userId: $userId,
            name: $this->validated('name'),
            phone: $this->validated('phone'),
            institution: $this->validated('institution'),
            major: $this->validated('major'),
            line: $this->validated('line') ?? null,
            nrp: $this->validated('nrp') ?? null,
            batch: $this->validated('batch') ?? null,
            ktmFile: $this->file('ktm_path'),
            idCardFile: $this->file('id_card_path')
        );
    }
}