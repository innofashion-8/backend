<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class AssignPermissionsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'Permissions array is required',
            'permissions.array' => 'Permissions must be an array',
            'permissions.*.exists' => 'One or more permissions do not exist',
        ];
    }
}
