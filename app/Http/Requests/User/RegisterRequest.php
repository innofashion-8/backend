<?php

namespace App\Http\Requests\User;

use App\Data\RegisterDTO;
use App\Enum\UserType;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends ApiRequest
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
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'type'        => ['required', new Enum(UserType::class)],
            
            'institution' => [
                Rule::requiredIf(function () {
                    return $this->input('type') === UserType::EXTERNAL->value;
                }),
                'nullable',
                'string',
                'max:255'
            ],
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
        ];
    }

    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Full name is required.',
            'name.string' => 'Full name must be a valid text.',
            'name.max' => 'Full name cannot exceed 255 characters.',

            // Email
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address has already been registered.',

            // Password
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a valid text.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',

            // Type
            'type.required' => 'User type is required.',

            // Institution
            'institution.required' => 'Institution/School name is required for external participants.',
            'institution.string' => 'Institution name must be a valid text.',
            'institution.max' => 'Institution name cannot exceed 255 characters.',

            // Phone
            'phone.required' => 'WhatsApp number is required.',
            'phone.string' => 'WhatsApp number must be a valid text.',
            'phone.regex' => 'WhatsApp number must be a valid Indonesian phone number (e.g., +628123456789 or 08123456789).',
            'phone.unique' => 'This WhatsApp number has already been registered by another user.',

            // Line
            'line.string' => 'LINE ID must be a valid text.',
            'line.regex' => 'LINE ID must be 4-20 characters and can only contain letters, numbers, dots, underscores, and hyphens.',
        ];
    }

    public function toDTO(): RegisterDTO
    {
        $data = $this->validated();

        return new RegisterDTO(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            type: UserType::from($data['type']),
            institution: $data['institution'] ?? null,
            phone: $data['phone'],
            line: $data['line'] ?? null,
        );
    }
}
