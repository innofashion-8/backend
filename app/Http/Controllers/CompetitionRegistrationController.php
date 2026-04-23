<?php

namespace App\Http\Controllers;

use App\Data\CompetitionFilterDTO;
use App\Data\SaveDraftDTO;
use App\Enum\ParticipantType;
use App\Enum\RegionType;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\User\Register\SaveCompetitionDraftRequest;
use App\Http\Requests\User\Register\SaveDraftRequest;
use App\Http\Requests\User\Register\SubmitCompetitionRequest;
use App\Http\Requests\User\SubmissionRequest;
use App\Services\CompetitionRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompetitionRegistrationsExport;

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

    public function exportRegistrations()
    {
        return Excel::download(new CompetitionRegistrationsExport, 'competition_registrations.xlsx');
    }

    public function checkStatus(Request $request, $key)
    {
        $user = $request->user();
        $competition = $this->registrationService->findCompetition($key);
        $registration = $this->registrationService->getDraft($user->id, $competition->id);

        $userProfile = [
            'name'         => $user->name,
            'email'        => $user->email,
            'type'         => $user->type,
            'nrp'          => $user->nrp,
            'major'        => $user->major,
            'batch'        => $user->batch,
            'phone'        => $user->phone,
            'ktm_path'     => $user->ktm_path,
            'id_card_path' => $user->id_card_path,
            'institution'  => $user->institution
        ];

        $isEligible = true;
        $ineligibilityReason = null;
        
        if ($user->type === UserType::INTERNAL && $competition->participant_type === ParticipantType::GROUP->value) {
            $isEligible = false;
            $ineligibilityReason = 'Mahasiswa (INTERNAL) tidak dapat mengikuti lomba Restyling. Lomba ini khusus untuk siswa SMP/SMA (Intermediate).';
        }

        if (!$registration) {
            return $this->success("Belum terdaftar", [
                'status'               => 'UNREGISTERED',
                'is_locked'            => false,
                'is_eligible'          => $isEligible,
                'ineligibility_reason' => $ineligibilityReason,
                'draft_data'           => null,
                'user_profile'         => $userProfile
            ]);
        }

        $waLink = null;
        if ($registration->status === StatusRegistration::VERIFIED) {
            $waLink = $registration->region === RegionType::NATIONAL
                      ? $competition->wa_link_national
                      : $competition->wa_link_international;  
        }

        return $this->success("Status registration fetched", [
            'registration_id'      => $registration->id,
            'status'               => $registration->status->value,
            'is_locked'            => $registration->status !== StatusRegistration::DRAFT,
            'is_eligible'          => $isEligible, 
            'ineligibility_reason' => $ineligibilityReason,
            
            'region'               => $registration->region ?? null,
            'category'             => $registration->category ?? null,
            'group_name'           => $registration->group_name ?? null,
            
            'draft_data'           => $registration->draft_data ?? (object)[],
            'user_profile'         => $userProfile,
            'wa_link'              => $waLink,
            'members'              => $registration->members, 
            'submissions'          => $registration->submissions
        ]);
    }

    public function saveDraft(SaveCompetitionDraftRequest $request, $key)
    {
        $competition = $this->registrationService->findCompetition($key);
        $payload = $request->validated()['draft_data'] ?? [];
       
        $existingDraft = $this->registrationService->getDraft($request->user()->id, $competition->id);
        $oldDraftData = $existingDraft ? ($existingDraft->draft_data ?? []) : [];

        if (isset($payload['members']) && is_array($payload['members'])) {
            
            foreach ($payload['members'] as $index => $memberData) {
                $file = $request->file("draft_data.members.{$index}.id_card");
                
                if ($file && $file->isValid()) {
                    // Hapus draft lama kalau ada
                    if (isset($oldDraftData['members'][$index]['id_card'])) {
                        $oldPath = $oldDraftData['members'][$index]['id_card'];
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                    
                    $path = $file->store('id_cards/draft', 'public');
                    $payload['members'][$index]['id_card'] = $path;

                } else {
                    // Kalau gak upload baru, kembaliin path lama (kalau ada)
                    if (isset($oldDraftData['members'][$index]['id_card'])) {
                        $payload['members'][$index]['id_card'] = $oldDraftData['members'][$index]['id_card'];
                    } else {
                        unset($payload['members'][$index]['id_card']); 
                    }
                }
            }
        }

        $dto = $request->toDTO(
            $request->user()->id,
            $competition->id,
            $payload 
        );

        $registration = $this->registrationService->saveDraft($dto);

        return $this->success("Draft berhasil disimpan", $registration);
    }

    public function submitFinal(SubmitCompetitionRequest $request, $key)
    {
        $competition = $this->registrationService->findCompetition($key);

        $memberFiles = [];
        $membersData = $request->input('members', []);
        
        foreach ($membersData as $index => $memberData) {
            $file = $request->file("members.{$index}.id_card");
            
            if ($file && $file->isValid()) {
                $email = $memberData['email'] ?? null; 
                
                if ($email) {
                    $path = $file->store('id_cards', 'public');
                    $memberFiles[$email] = $path; 
                }
            }
        }

        $dto = $request->toDTO(
            $request->user()->id,
            $competition->id,
            $memberFiles
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

    public function uploadSubmission(SubmissionRequest $request, $key)
    {
        $user = $request->user();
        $competition = $this->registrationService->findCompetition($key);
        
        $userName = str_replace(' ', '_', $user->name);
        $competitionName = str_replace(' ', '_', $competition->name);
        $timestamp = now()->format('YmdHis');
        
        $artworkFile = $request->file('artwork');
        $artworkFileName = "{$userName}_{$competitionName}_{$timestamp}.pdf";
        $artworkPath = $artworkFile->storeAs('submissions/artwork', $artworkFileName, 'public');
        
        $conceptFile = $request->file('concept');
        $conceptFileName = "{$userName}_Concept_{$competitionName}_{$timestamp}.pdf";
        $conceptPath = $conceptFile->storeAs('submissions/concept', $conceptFileName, 'public');

        $dto = $request->toDTO($user->id, $competition->id, $artworkPath, $conceptPath);

        $this->registrationService->uploadSubmission($dto);

        return $this->success("Karya dan Konsep berhasil dikumpulkan!");
    }
}
