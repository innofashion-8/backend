<?php

namespace App\Http\Requests\User\Register;

use App\Data\SubmitEventDTO;
use App\Enum\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitEventRequest extends FormRequest
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

        return [
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],

            // 'nrp'           => ['nullable', 'string', 'max:20', 
            //                     Rule::unique('users', 'nrp')->ignore($user->id)],
            // 'batch'         => ['nullable', 'integer', 'min:2018', 'max:' . date('Y')],
            // 'major'         => ['required', 'string'],
        ];
    }

    public function toDTO(
        string $userId, 
        string $eventId, 
        ?string $uploadedPaymentPath = null
    ): SubmitEventDTO
    {
        $data = $this->validated();

        return new SubmitEventDTO(
            userId: $userId,
            eventId: $eventId,
            
            paymentProof: $uploadedPaymentPath, 
            
            // nrp: $data['nrp'] ?? null,
            // batch: $data['batch'] ?? null,
            // major: $data['major'] ?? null,
        );
    }
}
