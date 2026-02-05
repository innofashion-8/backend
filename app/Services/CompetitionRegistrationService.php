<?php

namespace App\Services;

use App\Data\SaveDraftDTO;
use App\Data\SubmitCompetitionDTO;
use App\Enum\StatusRegistration;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        if (!$registration) {
             throw ValidationException::withMessages(['status' => ['Silakan simpan draft terlebih dahulu.']]);
        }

        if ($registration->status !== StatusRegistration::DRAFT) {
            throw ValidationException::withMessages(['status' => ['Data sudah disubmit. Tidak bisa submit ulang.']]);
        }

        $draft = $registration->draft_data ?? [];

        $finalPayment = $dto->paymentProof ?? $draft['payment_proof'] ?? null;
        if (!$finalPayment) {
            throw ValidationException::withMessages(['payment_proof' => ['Bukti pembayaran wajib diupload.']]);
        }

        $finalKtm = $dto->ktmPath ?? $draft['ktm_path'] ?? null;
        if ($dto->nrp && !$finalKtm) { 
             throw ValidationException::withMessages(['ktm_path' => ['KTM wajib diupload untuk mahasiswa internal.']]);
        }

        $finalIdCard = $dto->idCardPath ?? $draft['id_card_path'] ?? null;
        if (!$dto->nrp && !$finalIdCard) {
             throw ValidationException::withMessages(['id_card_path' => ['Kartu Identitas wajib diupload untuk peserta eksternal.']]);
        }

        $registration->update([
            'status'        => StatusRegistration::PENDING,
            
            'payment_proof' => $finalPayment,
            
            'nrp'           => $dto->nrp,
            'batch'         => $dto->batch,
            'major'         => $dto->major,
            
            'ktm_path'      => $finalKtm,
            'id_card_path'  => $finalIdCard,
            
            'draft_data'    => null,
        ]);
        
        return $registration;
    }
}