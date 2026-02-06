<?php

namespace App\Services;

use App\Data\SaveDraftDTO;
use App\Data\SubmitCompetitionDTO;
use App\Enum\StatusRegistration;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompetitionRegistrationService 
{
    protected CompetitionRegistration $registration;

    public function findCompetition(string $key): Competition
    {
        if (Str::isUuid($key)) {
            $competition = Competition::where('id', $key)->first();
        } else {
            $competition = Competition::where('slug', $key)->first();
        }

        if (!$competition) {
            throw new ModelNotFoundException("Kompetisi dengan key '{$key}' tidak ditemukan.");
        }

        return $competition;
    }

    public function __construct(CompetitionRegistration $registration)
    {
        $this->registration = $registration;
    }
    public function saveDraft(SaveDraftDTO $dto): CompetitionRegistration
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('competition_id', $dto->activityId)
            ->first();

        if ($registration && $registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages([
                'status' => ['Data sudah disubmit (Final). Anda tidak bisa mengubah draft lagi.']
            ]);
        }

        return $this->registration->updateOrCreate(
            [
                'user_id' => $dto->userId,
                'competition_id' => $dto->activityId,
            ],
            [
                'draft_data' => $dto->draftData,
                'status' => StatusRegistration::DRAFT,
            ]
        );
    }
    
    public function getDraft(string $userId, string $competitionId): ?CompetitionRegistration
    {
        return $this->registration->where('user_id', $userId)
            ->where('competition_id', $competitionId)
            ->first();
    }

    public function submitFinal(SubmitCompetitionDTO $dto): CompetitionRegistration
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('competition_id', $dto->competitionId)
            ->first();

        if (!$registration || $registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages(['status' => ['Invalid registration status or draft not found.']]);
        }

        $draft = $registration->draft_data ?? [];
        $user  = User::find($dto->userId);

        $rollbackActions = [];

        $processFile = function($newPath, $draftPath, $masterPath, $targetFolder) use (&$rollbackActions) {
            
            // FILE BARU (Upload)
            if ($newPath) {
                // Hapus draft lama (Cleanup biasa)
                if ($draftPath && Storage::disk('public')->exists($draftPath)) {
                    Storage::disk('public')->delete($draftPath);
                }

                // ROLLBACK PLAN: Kalau DB Error, hapus file baru ini biar gak nyampah
                $rollbackActions[] = function() use ($newPath) {
                    Storage::disk('public')->delete($newPath);
                    Log::info("Rollback: Menghapus file baru {$newPath}");
                };

                return $newPath; 
            }

            // FILE DRAFT (Move)
            if ($draftPath && Storage::disk('public')->exists($draftPath)) {
                if (str_contains($draftPath, '/draft/')) {
                    $filename  = basename($draftPath);
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
                return $draftPath;
            }

            // FILE MASTER
            if ($masterPath) {
                return $masterPath;
            }

            return null;
        };

        
        $finalPayment = $processFile($dto->paymentProof, $draft['payment_proof'] ?? null, null, 'payments');
        if (!$finalPayment) throw ValidationException::withMessages(['payment_proof' => ['Bukti pembayaran wajib diupload.']]);

        $finalKtm = $processFile($dto->ktmPath, $draft['ktm_path'] ?? null, $user->ktm_path, 'ktm');
        if ($dto->nrp && !$finalKtm) throw ValidationException::withMessages(['ktm_path' => ['KTM wajib diupload.']]);

        $finalIdCard = $processFile($dto->idCardPath, $draft['id_card_path'] ?? null, $user->id_card_path, 'id_card');
        if (!$dto->nrp && !$finalIdCard) throw ValidationException::withMessages(['id_card_path' => ['ID Card wajib diupload.']]);

        DB::beginTransaction();

        try {
            $registration->update([
                'status'        => StatusRegistration::PENDING,
                'payment_proof' => $finalPayment,
                'draft_data'    => null, 
            ]);

            $user->update([
                'nrp'          => $dto->nrp ?? $user->nrp,
                'batch'        => $dto->batch ?? $user->batch,
                'major'        => $dto->major ?? $user->major,
                'ktm_path'     => $finalKtm,
                'id_card_path' => $finalIdCard,
                'phone'        => $dto->extraData['phone'] ?? $user->phone,
                'institution'  => $dto->extraData['institution'] ?? $user->institution,
            ]);

            DB::commit();

            return $registration;

        } catch (\Exception $e) {
            
            DB::rollBack();

            Log::error("Terjadi Error DB, Memulai File Rollback...");
            foreach ($rollbackActions as $rollback) {
                $rollback();
            }

            Log::error("Error Submit Final: " . $e->getMessage());

            throw $e; 
        }
    }
}