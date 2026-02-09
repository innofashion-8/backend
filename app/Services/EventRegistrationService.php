<?php

namespace App\Services;

use App\Data\SaveDraftDTO;
use App\Data\SubmitEventDTO;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
                'status' => ['Data sudah disubmit (Final). Anda tidak bisa mengubah draft lagi.']
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

    public function submitFinal(SubmitEventDTO $dto): EventRegistration
    {
        $registration = $this->registration->with('user')
            ->where('user_id', $dto->userId)
            ->where('event_id', $dto->eventId)
            ->first();
        if (!$registration || $registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages(['status' => ['Invalid registration status or draft not found.']]);
        }

        $draft = $registration->draft_data ?? [];

        $user = $registration->user;

        $isInternal = $user->type === UserType::INTERNAL;

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

        DB::beginTransaction();
        try {
            $registration->update([
                'status' => StatusRegistration::PENDING,
                'draft_data' => null,
                'payment_proof' => $finalPayment,
            ]);

            $user->update([
                'nrp'          => $finalNrp,
                'batch'        => $finalBatch,
                'major'        => $dto->major ?? $user->major,
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