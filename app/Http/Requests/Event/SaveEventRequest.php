<?php

namespace App\Http\Requests\Event;

use App\Data\EventDTO;
use App\Enum\EventCategory;
use App\Http\Requests\ApiRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaveEventRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')->can('manage_events');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'category'    => ['required', new Enum(EventCategory::class)],
            'description' => 'nullable|string',
            'price'       => 'required|integer|min:0',
            'quota'       => 'required|integer|min:1',
            'wa_link'     => 'required|url',
            'start_time'  => 'required|date',
            'is_active'   => 'sometimes|boolean',
            'bank_name'            => 'nullable|string|max:100',
            'bank_account_name'    => 'nullable|string|max:100',
            'bank_account_number'  => 'nullable|string|max:50',
            'transfer_note_format' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // Title
            'title.required' => 'Event title is required.',
            'title.string' => 'Event title must be a valid text.',
            'title.max' => 'Event title cannot exceed 255 characters.',

            // Category
            'category.required' => 'Event category is required.',

            // Description
            'description.string' => 'Description must be a valid text.',

            // Price
            'price.required' => 'Event price is required.',
            'price.integer' => 'Event price must be a valid number.',
            'price.min' => 'Event price cannot be negative.',

            // Quota
            'quota.required' => 'Event quota is required.',
            'quota.integer' => 'Event quota must be a valid number.',
            'quota.min' => 'Event quota must be at least 1.',

            // WhatsApp Link
            'wa_link.required' => 'WhatsApp group link is required.',
            'wa_link.url' => 'WhatsApp group link must be a valid URL.',

            // Start Time
            'start_time.required' => 'Event start time is required.',
            'start_time.date' => 'Event start time must be a valid date.',

            // Is Active
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    public function toDTO(): EventDTO
    {
        $data = $this->validated();
        return new EventDTO(
            title: $data['title'],
            category: $data['category'],
            description: $data['description'],
            price: $data['price'],
            quota: $data['quota'],
            wa_link: $data['wa_link'],
            start_time: Carbon::parse($data['start_time']),
            is_active: $data['is_active'] ?? true,
            bank_name: $data['bank_name'] ?? null,
            bank_account_name: $data['bank_account_name'] ?? null,
            bank_account_number: $data['bank_account_number'] ?? null,
            transfer_note_format: $data['transfer_note_format'] ?? null,
        );
    }
}
