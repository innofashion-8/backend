<?php

namespace App\Http\Controllers;

use App\Data\EventFilterDTO;
use App\Data\SaveDraftDTO;
use App\Enum\StatusRegistration;
use App\Http\Requests\User\Register\SaveDraftRequest;
use App\Http\Requests\User\Register\SubmitEventRequest;
use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventRegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(EventRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function index(Request $request)
    {
        $filters = EventFilterDTO::fromRequest($request);
        $registrations = $this->registrationService->getAll($filters);
        return $this->success("Data fetched successfully", $registrations);
    }

    public function checkStatus(Request $request, $key)
    {
        $user = $request->user();
        $event = $this->registrationService->findEvent($key);
        $registration = $this->registrationService->getDraft($user->id, $event->id);

        $userProfile = [
            'nrp'      => $user->nrp,
            'major'    => $user->major,
            'batch'    => $user->batch,
            'phone'    => $user->phone,
            'ktm_path' => $user->ktm_path,
            'id_card_path' => $user->id_card_path,
            'institution' => $user->institution
        ];

        if (!$registration) {
            return $this->success("Belum terdaftar", [
                'status'       => 'UNREGISTERED',
                'is_locked'    => false,
                'draft_data'   => null,
                'user_profile' => $userProfile
            ]);
        }

        return $this->success("Status registration fetched", [
            'registration_id' => $registration->id,
            'status'          => $registration->status->value,
            'is_locked'       => $registration->status !== StatusRegistration::DRAFT,
            'draft_data'      => $registration->draft_data ?? (object)[],
            'user_profile'    => $userProfile
        ]);
    }
    
    public function saveDraft(SaveDraftRequest $request, $key)
    {
        $event = $this->registrationService->findEvent($key);
        $payload = $request->validated()['draft_data'];

        $existingDraft = $this->registrationService->getDraft($request->user()->id, $event->id);
        if ($request->hasFile("draft_data.payment_proof")) {
            if ($existingDraft && isset($existingDraft->draft_data['payment_proof'])){
                $oldPath = $existingDraft->draft_data['payment_proof'];
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file("draft_data.payment_proof")->store('payments/draft', 'public');
            $payload['payment_proof'] = $path;
        } else {
            if ($existingDraft && isset($existingDraft->draft_data['payment_proof'])){
                $payload['payment_proof'] = $existingDraft->draft_data['payment_proof'];
            } else {
                $payload['payment_proof'] = null;
            }
        }
        
        $dto = new SaveDraftDTO(
            userId: $request->user()->id,
            activityId: $event->id,
            draftData: $payload
        );
        
        $registration = $this->registrationService->saveDraft($dto);
        return $this->success("Draft berhasil disimpan.", $registration);
    }

    public function submitFinal(SubmitEventRequest $request, $key)
    {
        $event = $this->registrationService->findEvent($key);

        $user = $request->user();

        $uploadedPaymentPath = null;
        if ($request->hasFile('payment_proof')) {
            $uploadedPaymentPath = $request->file('payment_proof')->store('payments', 'public');
        }

        $dto = $request->toDTO(
            userId: $request->user()->id,
            eventId: $event->id,
            uploadedPaymentPath: $uploadedPaymentPath
        );

        $registration = $this->registrationService->submitFinal($dto);
        return $this->success("Pendaftaran event berhasil disubmit.", $registration);
    }
}
