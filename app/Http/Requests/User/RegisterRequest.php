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
            'phone.regex'   => 'Format nomor telepon tidak valid. Gunakan awalan 08, 628, atau +628.',
            'line.regex' => 'Line ID hanya boleh mengandung huruf, angka, titik, strip, dan underscore (4-20 karakter).',
            'institution.required' => 'Peserta Eksternal wajib mengisi asal institusi/sekolah.',
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
