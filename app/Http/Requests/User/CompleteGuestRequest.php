<?php

namespace App\Http\Requests\User;

use App\Data\CompleteGuestDTO;
use App\Http\Requests\ApiRequest;

class CompleteGuestRequest extends ApiRequest
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
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{7,14}$/',
                'unique:users,phone,' . $user->id,
            ],
            'institution' => [
                'nullable',
                'string',
                'max:150',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex'  => 'WhatsApp number must be a valid international number starting with country code (e.g., +628123456789).',
            'phone.unique' => 'This WhatsApp number has already been registered by another user.',
        ];
    }

    public function toDTO(): CompleteGuestDTO
    {
        return new CompleteGuestDTO(
            user: $this->user(),
            name: $this->validated('name') ?? null,
            phone: $this->validated('phone') ?? null,
            institution: $this->validated('institution') ?? null,
        );
    }
}
