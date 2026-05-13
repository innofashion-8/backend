<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class DivisionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $divisionId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('divisions', 'slug')->ignore($divisionId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Division name is required',
            'slug.required' => 'Division slug is required',
            'slug.regex' => 'Slug must be lowercase alphanumeric with hyphens only',
            'slug.unique' => 'This slug is already taken',
        ];
    }
}
