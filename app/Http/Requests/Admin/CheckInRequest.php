<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class CheckInRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('scan_attendance'); 
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_id.required' => 'Protocol ID wajib dikirim oleh scanner.',
        ];
    }
}