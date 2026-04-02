<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;

class DraftRegisterRequest extends ApiRequest
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
            'draft_data.ktm_path' => [
                'nullable', 
                'file', 
                'mimes:jpg,jpeg,png,pdf',
                'max:2048'
            ],

            'draft_data.id_card_path' => [
                'nullable', 
                'file', 
                'mimes:jpg,jpeg,png,pdf', 
                'max:2048'
            ],
            'draft_data.nrp'       => 'nullable|string|max:50',
            'draft_data.major'     => 'nullable|string|max:100',
            'draft_data.batch'     => 'nullable|integer|min:2018|max:' . date('Y'),
            'draft_data.institution' => 'nullable|string|max:100',
            'draft_data.phone'       => [
                'nullable', 
                'string', 
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/',
                'unique:users,phone,' . $this->user()->id
            ],
            'draft_data.line'     => [
                'nullable', 
                'string', 
                'regex:/^[a-zA-Z0-9._-]{4,20}$/' 
            ],
        ];
    }

    public function messages()
    {
        return [
            // Draft Data
            'draft_data.required' => 'Draft data is required.',
            'draft_data.array' => 'Draft data must be a valid array.',

            // KTM Path
            'draft_data.ktm_path.file' => 'Student ID Card must be a valid file.',
            'draft_data.ktm_path.mimes' => 'Student ID Card must be in JPG, JPEG, PNG, or PDF format.',
            'draft_data.ktm_path.max' => 'Student ID Card file size cannot exceed 2MB.',

            // ID Card Path
            'draft_data.id_card_path.file' => 'Identity Card must be a valid file.',
            'draft_data.id_card_path.mimes' => 'Identity Card must be in JPG, JPEG, PNG, or PDF format.',
            'draft_data.id_card_path.max' => 'Identity Card file size cannot exceed 2MB.',

            // NRP
            'draft_data.nrp.string' => 'NRP must be a valid text.',
            'draft_data.nrp.max' => 'NRP cannot exceed 50 characters.',

            // Major
            'draft_data.major.string' => 'Major/Department must be a valid text.',
            'draft_data.major.max' => 'Major/Department cannot exceed 100 characters.',

            // Batch
            'draft_data.batch.integer' => 'Batch/Year must be a valid number.',
            'draft_data.batch.min' => 'Batch/Year must be at least 2018.',
            'draft_data.batch.max' => 'Batch/Year cannot exceed the current year.',

            // Institution
            'draft_data.institution.string' => 'Institution name must be a valid text.',
            'draft_data.institution.max' => 'Institution name cannot exceed 100 characters.',

            // Phone
            'draft_data.phone.string' => 'WhatsApp number must be a valid text.',
            'draft_data.phone.regex' => 'WhatsApp number must be a valid Indonesian phone number (e.g., +628123456789 or 08123456789).',
            'draft_data.phone.unique' => 'This WhatsApp number has already been registered by another user.',

            // Line
            'draft_data.line.string' => 'LINE ID must be a valid text.',
            'draft_data.line.regex' => 'LINE ID must be 4-20 characters and can only contain letters, numbers, dots, underscores, and hyphens.',
        ];
    }
}
