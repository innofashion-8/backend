<?php

namespace App\Http\Requests\User;

use App\Data\UploadSubmissionDTO;
use Illuminate\Foundation\Http\FormRequest;

class SubmissionRequest extends FormRequest
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
            'artwork' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'concept' => ['required', 'file', 'mimes:pdf', 'max:5120']
        ];
    }

    public function messages(): array
    {
        return [
            // Artwork
            'artwork.required' => 'Artwork file is required.',
            'artwork.file' => 'Artwork must be a valid file.',
            'artwork.mimes' => 'Artwork must be in PDF format.',
            'artwork.max' => 'Artwork file size cannot exceed 5MB.',

            // Concept
            'concept.required' => 'Concept file is required.',
            'concept.file' => 'Concept must be a valid file.',
            'concept.mimes' => 'Concept must be in PDF format.',
            'concept.max' => 'Concept file size cannot exceed 5MB.',
        ];
    }

    public function toDTO(
        string $userId,
        string $competitionId,
        string $artworkPath,
        string $conceptPath
    ): UploadSubmissionDTO
    {
        return new UploadSubmissionDTO(
            userId: $userId,
            competitionId: $competitionId,
            artworkPath: $artworkPath,
            conceptPath: $conceptPath
        );
    }
}
