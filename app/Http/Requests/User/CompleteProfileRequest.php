<?php

namespace App\Http\Requests\User;

use App\Data\CompleteProfileDTO;
use App\Enum\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteProfileRequest extends FormRequest
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
        $user = $this->user();
        $isInternal = $user->type === UserType::INTERNAL;
        $draft = $user->draft_data ?? [];

        // Cek apakah file sudah aman (ada di master ATAU di draft)
        $hasKtm = !empty($user->ktm_path) || !empty($draft['ktm_path']);
        $hasIdCard = !empty($user->id_card_path) || !empty($draft['id_card_path']);

        $rules = [
            'major' => 'required|string|max:100',
        ];

        if ($isInternal) {
            $rules['nrp']   = 'required|string|max:50';
            $rules['batch'] = 'required|string|max:4';
            
            $rules['ktm_path'] = $hasKtm 
                ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                : 'required|image|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['id_card_path'] = $hasIdCard
                ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                : 'required|image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }

    public function toDTO(): CompleteProfileDTO
    {
        return new CompleteProfileDTO(
            user: $this->user(),
            major: $this->validated('major'),
            nrp: $this->validated('nrp') ?? null,
            batch: $this->validated('batch') ?? null,
            ktm: $this->file('ktm_path'),
            idCard: $this->file('id_card_path'),
        );
    }
}
