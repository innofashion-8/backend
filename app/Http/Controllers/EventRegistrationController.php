<?php

namespace App\Http\Controllers;

use App\Enum\StatusRegistration;
use App\Http\Requests\User\Register\SaveDraftRequest;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;

class EventRegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(EventRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function checkStatus(Request $request, $key)
    {
        $event = $this->registrationService->findEvent($key);

        $registration = $this->registrationService->getDraft($request->user()->id, $event->id);

        if (!$registration) {
            return $this->success("Belum terdaftar", [
                'status' => 'UNREGISTERED',
                'is_locked' => false,
                'draft_data' => null 
            ]);
        }

        return $this->success("Status registration fetched", [
            'registration_id' => $registration->id,
            'status'          => $registration->status->value,
            'is_locked'       => $registration->status !== StatusRegistration::DRAFT,
            'rejection_reason'=> $registration->rejection_reason,
            'draft_data'       => $registration->draft_data ?? (object)[], 
        ]);
    }
    
    public function saveDraft(SaveDraftRequest $request, $key)
    {
        $event = $this->registrationService->findEvent($key);
        $dto = $request->toDTO($request->user()->id, $event->id);
        $registration = $this->registrationService->saveDraft($dto);
        return $this->success("Draft berhasil disimpan.", $registration);
    }
}
