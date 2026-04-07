<?php

namespace App\Http\Requests\User;

use App\Data\CompleteRegisterDTO;
use App\Enum\UserType;
use App\Http\Requests\ApiRequest;

class CompleteRegisterRequest extends ApiRequest
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
        $user = $this->user();
        $isInternal = $user->type === UserType::INTERNAL;
        $draft = $user->draft_data ?? [];

        // Cek apakah file sudah aman (ada di master ATAU di draft)
        $hasKtm = !empty($user->ktm_path) || !empty($draft['ktm_path']);
        $hasIdCard = !empty($user->id_card_path) || !empty($draft['id_card_path']);

        $rules = [
            'institution' => 'required|string|max:100',
            'phone'       => [
                'required', 
                'string', 
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/',
                'unique:users,phone'
            ],
            'line'     => [
                'nullable', 
                'string', 
                'regex:/^[a-zA-Z0-9._-]{4,20}$/' 
            ],
            'major' => 'required|string|max:100',
        ];

        if ($isInternal) {
            $rules['nrp']   = 'required|string|max:50';
            $rules['batch'] = 'required|string|max:4';
            
            $rules['ktm_path'] = $hasKtm 
                ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                : 'required|image|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['id_card_path'] = $hasIdCard
                ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                : 'required|image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            // Institution
            'institution.required' => 'Institution name is required.',
            'institution.string' => 'Institution name must be a valid text.',
            'institution.max' => 'Institution name cannot exceed 100 characters.',

            // Phone
            'phone.required' => 'WhatsApp number is required.',
            'phone.string' => 'WhatsApp number must be a valid text.',
            'phone.regex' => 'WhatsApp number must be a valid Indonesian phone number (e.g., +628123456789 or 08123456789).',
            'phone.unique' => 'This WhatsApp number has already been registered by another user.',

            // Line
            'line.string' => 'LINE ID must be a valid text.',
            'line.regex' => 'LINE ID must be 4-20 characters and can only contain letters, numbers, dots, underscores, and hyphens.',

            // Major
            'major.required' => 'Major/Department is required.',
            'major.string' => 'Major/Department must be a valid text.',
            'major.max' => 'Major/Department cannot exceed 100 characters.',

            // NRP (Internal)
            'nrp.required' => 'NRP (Student ID) is required for internal users.',
            'nrp.string' => 'NRP must be a valid text.',
            'nrp.max' => 'NRP cannot exceed 50 characters.',

            // Batch (Internal)
            'batch.required' => 'Batch/Year is required for internal users.',
            'batch.string' => 'Batch/Year must be a valid text.',
            'batch.max' => 'Batch/Year cannot exceed 4 characters.',

            // KTM Path (Internal)
            'ktm_path.required' => 'Student ID Card (KTM) is required.',
            'ktm_path.image' => 'Student ID Card must be an image file.',
            'ktm_path.mimes' => 'Student ID Card must be in JPEG, PNG, or JPG format.',
            'ktm_path.max' => 'Student ID Card file size cannot exceed 2MB.',

            // ID Card Path (External)
            'id_card_path.required' => 'Identity Card is required.',
            'id_card_path.image' => 'Identity Card must be an image file.',
            'id_card_path.mimes' => 'Identity Card must be in JPEG, PNG, or JPG format.',
            'id_card_path.max' => 'Identity Card file size cannot exceed 2MB.',
        ];
    }

    public function toDTO(): CompleteRegisterDTO
    {
        return new CompleteRegisterDTO(
            user: $this->user(),
            phone: $this->validated('phone'),
            line: $this->validated('line') ?? null,
            major: $this->validated('major'),
            institution: $this->validated('institution') ?? null,
            nrp: $this->validated('nrp') ?? null,                
            batch: $this->validated('batch') ?? null,            
            ktm: $this->file('ktm_path'),
            idCard: $this->file('id_card_path'),
        );
    }
}
