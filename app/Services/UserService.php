<?php

namespace App\Services;

use App\Data\CompleteProfileDTO;
use App\Data\ProfileDraftDTO;
use App\Enum\UserType;
use App\Models\CompetitionRegistration;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsers(): Collection
    {
        return $this->user->with(['eventRegistrations.event', 'competitionRegistrations.competition'])->latest()->get();
    }

    public function getUser(string $id): ?User
    {
        return $this->user->with(['eventRegistrations.event', 'competitionRegistrations.competition'])->find($id);
    }

    public function getDraft(string $userId)
    {
        return $this->user->where('id', $userId)->first();
    }

    public function saveDraft(ProfileDraftDTO $dto)
    {
        $user = $this->user->where('id', $dto->userId)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['User not found.']
            ]);
        }

        $user->update([
            'draft_data' => $dto->draftData,
        ]);

        return $user;
    }

    public function completeProfile(CompleteProfileDTO $dto): User
    {
        $user = $dto->user;
        $isInternal = $user->type === UserType::INTERNAL;
        $draft = $user->draft_data ?? [];
        $rollbackActions = [];

        $processProfileFile = function($newUploadedFile, $draftPath, $masterPath, $targetFolder) use (&$rollbackActions) {
            
            // 1. FILE BARU (Upload)
            if ($newUploadedFile) {
                if ($draftPath && Storage::disk('public')->exists($draftPath)) {
                    Storage::disk('public')->delete($draftPath);
                }
                $newPath = $newUploadedFile->store($targetFolder, 'public');
                
                $rollbackActions[] = function() use ($newPath) {
                    Storage::disk('public')->delete($newPath);
                    Log::info("Rollback: Menghapus file profile baru {$newPath}");
                };
                return $newPath;
            }

            if ($draftPath && Storage::disk('public')->exists($draftPath)) {
                $filename = basename($draftPath);
                $finalPath = "{$targetFolder}/{$filename}";

                try {
                    Storage::disk('public')->move($draftPath, $finalPath);
                    
                    $rollbackActions[] = function() use ($finalPath, $draftPath) {
                         if (Storage::disk('public')->exists($finalPath)) {
                             Storage::disk('public')->move($finalPath, $draftPath);
                             Log::info("Rollback: Mengembalikan file {$finalPath} ke {$draftPath}");
                         }
                    };
                    return $finalPath;
                } catch (\Exception $e) {
                    return $draftPath;
                }
            }

            if ($masterPath) {
                return $masterPath;
            }

            return null;
        };

        $finalKtm = null;
        $finalIdCard = null;

        if ($isInternal) {
            $finalKtm = $processProfileFile($dto->ktm, $draft['ktm_path'] ?? null, $user->ktm_path, 'uploads/documents/ktm');
            if (!$finalKtm) throw ValidationException::withMessages(['ktm' => ['KTM wajib diupload.']]);
        } else {
            $finalIdCard = $processProfileFile($dto->idCard, $draft['id_card_path'] ?? null, $user->id_card_path, 'uploads/documents/id_card');
            if (!$finalIdCard) throw ValidationException::withMessages(['id_card' => ['Kartu identitas wajib diupload.']]);
        }


        DB::beginTransaction();
        try {
            $dataToUpdate = [
                'major'      => $dto->major,
                'draft_data' => null,
            ];

            if ($isInternal) {
                $dataToUpdate['nrp']   = $dto->nrp;
                $dataToUpdate['batch'] = $dto->batch;
                $dataToUpdate['ktm_path'] = $finalKtm;
            } else {
                $dataToUpdate['id_card_path'] = $finalIdCard;
            }

            $user->update($dataToUpdate);

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Terjadi Error DB Profile, Memulai File Rollback...");
            foreach ($rollbackActions as $rollback) {
                $rollback();
            }
            Log::error("Error Complete Profile: " . $e->getMessage());
            
            throw $e;
        }
    }

    public function getRegistrations(string $userId): array
    {
        $competitions = CompetitionRegistration::with('competition')
            ->where('user_id', $userId)
            ->get();

        $events = EventRegistration::with('event')
            ->where('user_id', $userId)
            ->get();
        
        return [
            'competitions' => $competitions,
            'events' => $events,
        ];
    }
}