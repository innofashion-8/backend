<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('roles', 'name')->where('guard_name', 'admin')->ignore($roleId),
            ],
            'display_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Role name is required',
            'name.regex' => 'Role name must be lowercase alphanumeric with underscores only',
            'name.unique' => 'This role name already exists',
        ];
    }
}
