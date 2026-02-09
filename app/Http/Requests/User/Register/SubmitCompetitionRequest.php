<?php

namespace App\Http\Requests\User\Register;

use App\Data\SubmitCompetitionDTO;
use App\Enum\UserType;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitCompetitionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isInternal = $user->type === UserType::INTERNAL;

        return [
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],

            'nrp'           => ['nullable', 'string', 'max:20', 
                                Rule::unique('users', 'nrp')->ignore($user->id)],
            'batch'         => ['nullable', 'integer', 'min:2018', 'max:' . date('Y')],
            'major'         => ['required', 'string'],

            'ktm_path'      => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'id_card_path'  => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function toDTO(
        string $userId, 
        string $competitionId, 
        ?string $uploadedPaymentPath = null, 
        ?string $uploadedKtmPath = null, 
        ?string $uploadedIdCardPath = null
    ): SubmitCompetitionDTO
    {
        $data = $this->validated();

        return new SubmitCompetitionDTO(
            userId: $userId,
            competitionId: $competitionId,
            
            paymentProof: $uploadedPaymentPath, 
            
            nrp: $data['nrp'] ?? null,
            batch: $data['batch'] ?? null,
            major: $data['major'] ?? null,
            
            ktmPath: $uploadedKtmPath,
            idCardPath: $uploadedIdCardPath,
        );
    }
}