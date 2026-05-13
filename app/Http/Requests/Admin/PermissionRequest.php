<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('permissions', 'name')->where('guard_name', 'admin')->ignore($permissionId),
            ],
            'display_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required',
            'name.regex' => 'Permission name must be lowercase alphanumeric with underscores only',
            'name.unique' => 'This permission name already exists',
        ];
    }
}
