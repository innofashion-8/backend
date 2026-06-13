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
        
        $fileId = $request->input('file_id');
        
        $artworkTempPath = storage_path("app/public/temp/{$fileId}_artwork.pdf");
        $conceptTempPath = storage_path("app/public/temp/{$fileId}_concept.pdf");

        if (!file_exists($artworkTempPath) || !file_exists($conceptTempPath)) {
            return response()->json([
                'code' => 422,
                'success' => false,
                'message' => 'The artwork or concept failed to upload completely.',
                'errors' => ['artwork' => ['The artwork or concept failed to upload completely.']]
            ], 422);
        }

        // Validate max size manually (5MB)
        if (filesize($artworkTempPath) > 5120 * 1024) {
            unlink($artworkTempPath);
            unlink($conceptTempPath);
            return response()->json(['code' => 422, 'success' => false, 'message' => 'Artwork file size cannot exceed 5MB.', 'errors' => ['artwork' => ['Artwork file size cannot exceed 5MB.']]], 422);
        }
        if (filesize($conceptTempPath) > 5120 * 1024) {
            unlink($artworkTempPath);
            unlink($conceptTempPath);
            return response()->json(['code' => 422, 'success' => false, 'message' => 'Concept file size cannot exceed 5MB.', 'errors' => ['concept' => ['Concept file size cannot exceed 5MB.']]], 422);
        }

        $userName = str_replace(' ', '_', $user->name);
        $competitionName = str_replace(' ', '_', $competition->name);
        $timestamp = now()->format('YmdHis');
        
        $artworkFileName = "{$userName}_{$competitionName}_{$timestamp}.pdf";
        $artworkPath = "submissions/artwork/{$artworkFileName}";
        Storage::disk('public')->put($artworkPath, file_get_contents($artworkTempPath));
        
        $conceptFileName = "{$userName}_Concept_{$competitionName}_{$timestamp}.pdf";
        $conceptPath = "submissions/concept/{$conceptFileName}";
        Storage::disk('public')->put($conceptPath, file_get_contents($conceptTempPath));

        @unlink($artworkTempPath);
        @unlink($conceptTempPath);

        $dto = $request->toDTO($user->id, $competition->id, $artworkPath, $conceptPath);

        $this->registrationService->uploadSubmission($dto);

        return $this->success("Karya dan Konsep berhasil dikumpulkan!");
    }

    public function uploadChunk(Request $request, $key)
    {
        $request->validate([
            'file' => 'required|file',
            'file_id' => 'required|string',
            'file_type' => 'required|string|in:artwork,concept',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
        ]);

        $fileId = $request->input('file_id');
        $fileType = $request->input('file_type');
        $chunkIndex = $request->input('chunk_index');
        $totalChunks = $request->input('total_chunks');
        $file = $request->file('file');

        $chunkDir = "chunks/{$fileId}";
        $chunkFileName = "{$fileType}_{$chunkIndex}.part";
        
        $file->storeAs($chunkDir, $chunkFileName, 'local');

        if ($chunkIndex == $totalChunks - 1) {
            $mergedPath = storage_path("app/public/temp/{$fileId}_{$fileType}.pdf");
            $dir = dirname($mergedPath);
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }

            $out = fopen($mergedPath, 'wb');
            for ($i = 0; $i < $totalChunks; $i++) {
                $partPath = \Illuminate\Support\Facades\Storage::disk('local')->path("{$chunkDir}/{$fileType}_{$i}.part");
                if (file_exists($partPath)) {
                    $in = fopen($partPath, 'rb');
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    @unlink($partPath);
                }
            }
            fclose($out);
            @rmdir(dirname($partPath));
        }

        return $this->success("Chunk {$chunkIndex} uploaded");
    }
}
