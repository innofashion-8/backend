<?php

namespace App\Services;

use App\Data\SaveDraftDTO;
use App\Enum\StatusRegistration;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventRegistrationService
{
    protected EventRegistration $registration;

    public function __construct(EventRegistration $registration)
    {
        $this->registration = $registration;
    }

    public function findEvent(string $key)
    {
        if (Str::isUuid($key)) {
            $event = Event::where('id', $key)->first();
        } else {
            $event = Event::where('slug', $key)->first();
        }

        if (!$event) {
            throw new ModelNotFoundException("Event dengan key '{$key}' tidak ditemukan.");
        }

        return $event;
    }

    public function saveDraft(SaveDraftDTO $dto): EventRegistration
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('event_id', $dto->activityId)
            ->first();

        if ($registration && $registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages([
                'status' => ['Anda sudah terdaftar. Tidak bisa edit draft lagi.']
            ]);
        }

        return $this->registration->updateOrCreate(
            [
                'user_id' => $dto->userId,
                'event_id' => $dto->activityId,
            ],
            [
                'draft_data' => $dto->draftData,
                'status' => StatusRegistration::DRAFT,
            ]
        );
    }

    public function getDraft(string $userId, string $eventId): ?EventRegistration
    {
        return $this->registration->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->first();
    }
}