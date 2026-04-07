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

    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Competition name is required.',
            'name.string' => 'Competition name must be a valid text.',
            'name.max' => 'Competition name cannot exceed 255 characters.',

            // Description
            'description.string' => 'Description must be a valid text.',

            // Participant Type
            'participant_type.required' => 'Participant type is required.',

            // Min Members
            'min_members.integer' => 'Minimum members must be a valid number.',
            'min_members.min' => 'Minimum members must be at least 1.',
            'min_members.required_if' => 'Minimum members is required for group competitions.',

            // Max Members
            'max_members.integer' => 'Maximum members must be a valid number.',
            'max_members.gte' => 'Maximum members must be greater than or equal to minimum members.',
            'max_members.required_if' => 'Maximum members is required for group competitions.',

            // WhatsApp Links
            'wa_link_national.required' => 'WhatsApp link for national participants is required.',
            'wa_link_national.url' => 'WhatsApp link for national participants must be a valid URL.',
            'wa_link_international.required' => 'WhatsApp link for international participants is required.',
            'wa_link_international.url' => 'WhatsApp link for international participants must be a valid URL.',

            // Is Active
            'is_active.boolean' => 'Active status must be true or false.',

            // Registration Timeline
            'registration_open_at.required' => 'Registration opening date is required.',
            'registration_open_at.date' => 'Registration opening date must be a valid date.',
            'registration_close_at.required' => 'Registration closing date is required.',
            'registration_close_at.date' => 'Registration closing date must be a valid date.',
            'registration_close_at.after' => 'Registration closing date must be after the opening date.',

            // Submission Timeline
            'submission_open_at.required' => 'Submission opening date is required.',
            'submission_open_at.date' => 'Submission opening date must be a valid date.',
            'submission_close_at.required' => 'Submission closing date is required.',
            'submission_close_at.date' => 'Submission closing date must be a valid date.',
            'submission_close_at.after' => 'Submission closing date must be after the opening date.',
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
