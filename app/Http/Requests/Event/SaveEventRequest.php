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
            'start_time'  => 'required|date',
            'is_active'   => 'sometimes|boolean',
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
            start_time: Carbon::parse($data['start_time']),
            is_active: $data['is_active'] ?? true,
        );
    }
}
