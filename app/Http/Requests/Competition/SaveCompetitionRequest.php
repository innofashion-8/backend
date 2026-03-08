<?php

namespace App\Http\Requests\Competition;

use App\Data\CompetitionDTO;
use App\Enum\ParticipantType;
use App\Http\Requests\ApiRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
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
            // 'category'    => ['required', new Enum(CompetitionCategory::class)],
            'description' => 'nullable|string',
            'participant_type' => ['required', Rule::enum(ParticipantType::class)],
            'min_members'           => ['nullable', 'integer', 'min:1', 'required_if:participant_type,' . ParticipantType::GROUP->value],
            'max_members'           => ['nullable', 'integer', 'gte:min_members', 'required_if:participant_type,' . ParticipantType::GROUP->value],
            'wa_link_national'      => ['required', 'url'],
            'wa_link_international'      => ['required', 'url'],
            // 'registration_fee'       => 'required|integer|min:0',
            'is_active'   => 'sometimes|boolean',
            'registration_open_at'  => ['required', 'date'],
            'registration_close_at' => ['required', 'date', 'after:registration_open_at'],
            
            // Timeline Submission
            'submission_open_at'    => ['required', 'date'],
            'submission_close_at'   => ['required', 'date', 'after:submission_open_at'],
        ];
    }

    public function toDTO(): CompetitionDTO
    {
        $data = $this->validated();
        return new CompetitionDTO(
            name: $data['name'],
            participant_type: $data['participant_type'],
            min_members: $data['min_members'],
            max_members: $data['max_members'],
            wa_link_national: $data['wa_link_national'],
            wa_link_international: $data['wa_link_international'],
            registration_open_at: Carbon::parse($data['registration_open_at']),
            registration_close_at: Carbon::parse($data['registration_close_at']),
            submission_open_at: Carbon::parse($data['submission_open_at']),
            submission_close_at: Carbon::parse($data['submission_close_at']),
            description: $data['description'],
            is_active: $data['is_active'] ?? true
        );
    }
}
