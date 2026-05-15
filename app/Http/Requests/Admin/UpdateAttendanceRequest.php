<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
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
            'attended' => ['required', 'string', 'in:pending,checked_in,checked_out'],
        ];
    }

    public function messages(): array
    {
        return [
            'attended.required' => 'Attendance status is required.',
            'attended.string' => 'Attendance status must be a string.',
            'attended.in' => 'Attendance status is invalid.',
        ];
    }
}
