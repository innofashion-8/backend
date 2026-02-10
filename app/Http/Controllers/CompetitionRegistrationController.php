<?php

namespace App\Http\Controllers;

use App\Data\CompetitionFilterDTO;
use App\Data\SaveDraftDTO;
use App\Enum\StatusRegistration;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\User\Register\SaveDraftRequest;
use App\Http\Requests\User\Register\SubmitCompetitionRequest;
use App\Models\CompetitionRegistration;
use App\Services\CompetitionRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompetitionRegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(CompetitionRegistrationService $competitionRegistrationService)
    {
        $this->registrationService = $competitionRegistrationService;
    }

    public function index(Request $request)
    {
        $filters = CompetitionFilterDTO::fromRequest($request);
        $registrations = $this->registrationService->getAll($filters);
        return $this->success("Data fetched successfully", $registrations);
    }

    public function checkStatus(Request $request, $key)
    {
        $user = $request->user();
        $competition = $this->registrationService->findCompetition($key);
        $registration = $this->registrationService->getDraft($user->id, $competition->id);

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
        $competition = $this->registrationService->findCompetition($key);

        $payload = $request->validated()['draft_data'];

        $fileFields = [
            'payment_proof' => 'payments/draft', 
            'ktm_path'      => 'ktm/draft', 
            'id_card_path'  => 'id/draft', 
        ];

        $existingDraft = $this->registrationService->getDraft($request->user()->id, $competition->id);

        foreach ($fileFields as $field => $folder) {
            $requestKey = "draft_data.{$field}";

            if ($request->hasFile($requestKey)) {
                if ($existingDraft && isset($existingDraft->draft_data[$field])) {
                    $oldPath = $existingDraft->draft_data[$field];
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file($requestKey)->store($folder, 'public');
                $payload[$field] = $path;
            } else {
                if ($existingDraft && isset($existingDraft->draft_data[$field])) {
                    $payload[$field] = $existingDraft->draft_data[$field];
                } else {
                    $payload[$field] = null;
                }
            }
        }

        $dto = new SaveDraftDTO(
            userId: $request->user()->id,
            activityId: $competition->id,
            draftData: $payload
        );

        $registration = $this->registrationService->saveDraft($dto);

        return $this->success("Draft berhasil disimpan", $registration);
    }

    public function submitFinal(SubmitCompetitionRequest $request, $key)
    {
        $competition = $this->registrationService->findCompetition($key);

        $paymentPath = null;
        $ktmPath = null;
        $idCardPath = null;

        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payments', 'public');
        }

        if ($request->hasFile('ktm_path')) {
            $ktmPath = $request->file('ktm_path')->store('ktm', 'public');
        }
        
        if ($request->hasFile('id_card_path')) {
            $idCardPath = $request->file('id_card_path')->store('id_card', 'public');
        }

        $dto = $request->toDTO(
            $request->user()->id,
            $competition->id,
            $paymentPath,
            $ktmPath,
            $idCardPath
        );

        $registration = $this->registrationService->submitFinal($dto);

        return $this->success("Pendaftaran berhasil disubmit! Silakan tunggu verifikasi admin.", $registration);
    }

    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        $dto = $request->toDTO($id);

        $registration = $this->registrationService->updateStatus($dto);

        return $this->success("Status pendaftaran berhasil diubah", $registration);
    }
}
