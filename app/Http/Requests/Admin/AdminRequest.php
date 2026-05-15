<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'nrp' => [
                'required',
                'string',
                'max:50',
                Rule::unique('admins', 'nrp')->ignore($adminId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'division_id' => ['required', 'uuid', 'exists:divisions,id'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Admin name is required',
            'nrp.required' => 'NRP is required',
            'nrp.unique' => 'This NRP is already registered',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be valid',
            'email.unique' => 'This email is already registered',
            'division_id.required' => 'Division is required',
            'division_id.exists' => 'Selected division does not exist',
            'role.exists' => 'Selected role does not exist',
        ];
    }
}
