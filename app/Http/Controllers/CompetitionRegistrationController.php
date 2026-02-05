<?php

namespace App\Http\Controllers;

use App\Enum\StatusRegistration;
use App\Http\Requests\User\Register\SaveDraftRequest;
use App\Models\CompetitionRegistration;
use App\Services\CompetitionRegistrationService;
use Illuminate\Http\Request;

class CompetitionRegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(CompetitionRegistrationService $competitionRegistrationService)
    {
        $this->registrationService = $competitionRegistrationService;
    }

    public function checkStatus(Request $request, $key)
    {
        $competition = $this->registrationService->findCompetition($key);

        $registration = $this->registrationService->getDraft($request->user()->id, $competition->id);

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
        $competition = $this->registrationService->findCompetition($key);

        $dto = $request->toDTO($request->user()->id, $competition->id);

        $registration = $this->registrationService->saveDraft($dto);

        return $this->success("Draft saved successfully", $registration);
    }
}
