<?php

namespace App\Http\Requests\Competition;

use App\Data\CompetitionDTO;
use App\Enum\CompetitionCategory;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaveCompetitionRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')->can('manage_competitions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'category'    => ['required', new Enum(CompetitionCategory::class)],
            'description' => 'nullable|string',
            'registration_fee'       => 'required|integer|min:0',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function toDTO(): CompetitionDTO
    {
        return new CompetitionDTO(
            name: $this->name,
            category: $this->category,
            description: $this->description,
            registration_fee: $this->registration_fee,
            is_active: $this->is_active
        );
    }
}
