<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class GoogleLoginRequest extends ApiRequest
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
            'token' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'token.required' => 'Google Token is required',
            'token.string' => 'Google Token must be a string',
        ];
    }
}
