<?php

namespace App\Services;

use App\Data\CompetitionFilterDTO;
use App\Data\SaveDraftDTO;
use App\Data\SubmitCompetitionDTO;
use App\Data\UpdateStatusDTO;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function getAll(CompetitionFilterDTO $filters): LengthAwarePaginator
    {
        $query = $this->registration->query()
            ->with(['user', 'competition']) 
            ->latest();

        if ($filters->competitionId) {
            $query->where('competition_id', $filters->competitionId);
        }

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->search) {
            $search = $filters->search;
            
            $query->whereHas('user', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nrp', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters->perPage)->withQueryString();
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
        $registration = $this->registration->with('user')
            ->where('user_id', $dto->userId)
            ->where('competition_id', $dto->competitionId)
            ->first();

        if (!$registration || $registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages(['status' => ['Invalid registration status or draft not found.']]);
        }

        $draft = $registration->draft_data ?? [];
        $user  = $registration->user;

        $isInternal = $user->type === UserType::INTERNAL;
        $isExternal = $user->type === UserType::EXTERNAL;

        $finalNrp   = $isInternal ? ($dto->nrp ?? $user->nrp) : null;
        $finalBatch = $isInternal ? ($dto->batch ?? $user->batch) : null;

        if ($isInternal) {
            if (empty($finalNrp)) {
                throw ValidationException::withMessages(['nrp' => ['NRP wajib diisi (tidak ditemukan di input maupun profil).']]);
            }
            if (empty($finalBatch)) {
                throw ValidationException::withMessages(['batch' => ['Angkatan wajib diisi.']]);
            }
        }

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

        $finalKtm = $isInternal ? $processFile($dto->ktmPath, $draft['ktm_path'] ?? null, $user->ktm_path, 'ktm') : null;
        if ($isInternal && !$finalKtm) { 
             throw ValidationException::withMessages(['ktm_path' => ['Mahasiswa Internal wajib upload KTM.']]);
        }

        $finalIdCard = $isExternal ? $processFile($dto->idCardPath, $draft['id_card_path'] ?? null, $user->id_card_path, 'id_card') : null;
        if ($isExternal && !$finalIdCard) {
            throw ValidationException::withMessages(['id_card_path' => ['Peserta Eksternal wajib upload Kartu Identitas.']]);
        }


        DB::beginTransaction();

        try {
            $registration->update([
                'status'        => StatusRegistration::PENDING,
                'payment_proof' => $finalPayment,
                'draft_data'    => null, 
            ]);

            $user->update([
                'nrp'          => $finalNrp,
                'batch'        => $finalBatch,
                'major'        => $dto->major ?? $user->major,
                'ktm_path'     => $finalKtm,
                'id_card_path' => $finalIdCard,
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

    public function updateStatus(UpdateStatusDTO $dto)
    {
        $registration = $this->registration->with('user')
                            ->where('id', $dto->registrationId)
                            ->first();

        if (!$registration) {
            throw ValidationException::withMessages(['id' => ['Pendaftaran tidak ditemukan.']]);
        }

        if ($registration->status === StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages(['status' => ['Pendaftaran sudah terverifikasi sebelumnya.']]);
        }

        $registration->update([
            'status' => StatusRegistration::from($dto->status),
            'rejection_reason' => $dto->rejection_reason,
        ]);

        try {
            if ($dto->status === StatusRegistration::VERIFIED->value) {
                // Mail::to($registration->user->email)->queue(new RegistrationVerified($registration));
            } elseif ($dto->status === StatusRegistration::REJECTED->value) {
                // Mail::to($registration->user->email)->queue(new RegistrationRejected($registration, $dto->rejection_reason));
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email status pendaftaran: " . $e->getMessage());
        }

        return $registration;
    }
}