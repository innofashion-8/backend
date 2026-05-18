<?php

namespace App\Services;

use App\Data\EventFilterDTO;
use App\Data\SaveDraftDTO;
use App\Data\SubmitEventDTO;
use App\Data\UpdateStatusDTO;
use App\Enum\AttendedStatus;
use App\Enum\QuestionType;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Mail\RegistrationRejected;
use App\Mail\RegistrationVerified;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationQuestion;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function findEvent(string $key): Event
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

    public function getAll(EventFilterDTO $filter)
    {
        $query = $this->registration->query()
            ->with(['user', 'event'])
            ->where('status', '!=', StatusRegistration::DRAFT)
            ->latest();

        if ($filter->eventId) {
            $query->where('event_id', $filter->eventId);
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        if ($filter->eventName) {
            $evtName = $filter->eventName;
            $query->whereHas('event', function ($q) use ($evtName) {
                $q->where('title', $evtName);
            });
        }

        if ($filter->userType) {
            $userType = $filter->userType;
            $query->whereHas('user', function ($q) use ($userType) {
                $q->where('type', $userType);
            });
        }

        if ($filter->attendedStatus) {
            $query->where('attended_status', $filter->attendedStatus);
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('nrp', 'like', '%' . $search . '%');
                })
                ->orWhereHas('event', function ($evtQ) use ($search) {
                    $evtQ->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        return $query->paginate($filter->perPage)->withQueryString();
    }

    public function saveDraft(SaveDraftDTO $dto): EventRegistration
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('event_id', $dto->activityId)
            ->first();

        if ($registration && !in_array($registration->status, [StatusRegistration::DRAFT, StatusRegistration::REJECTED])) {
            throw ValidationException::withMessages([
                'status' => ['Data sudah disubmit (Final). Anda tidak bisa mengubah data lagi.']
            ]);
        }

        $statusToSave = ($registration && $registration->status === StatusRegistration::REJECTED) 
                        ? StatusRegistration::REJECTED 
                        : StatusRegistration::DRAFT;

        return $this->registration->updateOrCreate(
            [
                'user_id' => $dto->userId,
                'event_id' => $dto->activityId,
            ],
            [
                'draft_data' => $dto->draftData,
                'status' => $statusToSave,
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

        if ($registration && !in_array($registration->status, [StatusRegistration::DRAFT, StatusRegistration::REJECTED])) {
            throw ValidationException::withMessages([
                'status' => ['Anda sudah terdaftar di event ini. Pendaftaran sedang diproses atau sudah diverifikasi.']
            ]);
        }

        $draft = $registration ? ($registration->draft_data ?? []) : [];

        // $user = $registration->user;

        // $isInternal = $user->type === UserType::INTERNAL;

        // $finalNrp   = $isInternal ? ($dto->nrp ?? $user->nrp) : null;
        // $finalBatch = $isInternal ? ($dto->batch ?? $user->batch) : null;

        // if ($isInternal) {
        //     if (empty($finalNrp)) {
        //         throw ValidationException::withMessages(['nrp' => ['NRP wajib diisi (tidak ditemukan di input maupun profil).']]);
        //     }
        //     if (empty($finalBatch)) {
        //         throw ValidationException::withMessages(['batch' => ['Angkatan wajib diisi.']]);
        //     }
        // }

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

        $event = $this->findEvent($dto->eventId);
        $isFree = $event->price == 0;

        $finalPayment = $processFile($dto->paymentProof, $draft['payment_proof'] ?? null, null, 'payments');
        if (!$isFree && !$finalPayment) throw ValidationException::withMessages(['payment_proof' => ['Bukti pembayaran wajib diupload.']]);

        DB::beginTransaction();
        try {
            $event = Event::where('id', $dto->eventId)->lockForUpdate()->first();

            if ($event->start_time && Carbon::now()->greaterThanOrEqualTo($event->start_time)) {
                throw ValidationException::withMessages([
                    'status' => ['Mohon maaf, pendaftaran sudah ditutup karena event sudah dimulai.']
                ]);
            }

            $currentParticipants = $event->eventRegistrations()
                ->whereIn('status', [StatusRegistration::PENDING, StatusRegistration::VERIFIED])
                ->count();

            if ($currentParticipants >= $event->quota) {
                throw ValidationException::withMessages([
                    'quota' => ['Mohon maaf, kuota pendaftaran untuk event ini sudah penuh.']
                ]);
            }

            $dataToSave = [
                'status'        => StatusRegistration::PENDING,
                'payment_proof' => $finalPayment,
                'draft_data'    => null, 
            ];

            if ($registration) {
                $registration->update($dataToSave);
            } else {
                $dataToSave['user_id'] = $dto->userId;
                $dataToSave['event_id'] = $dto->eventId;
                $registration = $this->registration->create($dataToSave);
            }


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
            'verified_by' => $dto->verifiedBy,
            'status' => StatusRegistration::from($dto->status),
            'rejection_reason' => $dto->status === StatusRegistration::REJECTED->value ? $dto->rejection_reason : null,
        ]);

        try {
            if ($dto->status === StatusRegistration::VERIFIED->value) {
                Mail::to($registration->user->email)->queue(new RegistrationVerified($registration));
            } elseif ($dto->status === StatusRegistration::REJECTED->value) {
                Mail::to($registration->user->email)->queue(new RegistrationRejected($registration, $dto->rejection_reason));
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email status pendaftaran: " . $e->getMessage());
        }

        return $registration;
    }

    public function processCheckIn(string $registrationId)
    {
        $registration = EventRegistration::with('user', 'event')->find($registrationId);
        $type = 'EVENT';
        $itemName = $registration ? $registration->event->title : '';

        if (!$registration) {
            throw ValidationException::withMessages([
                'registration_id' => ['Data pendaftaran tidak ditemukan di sistem.']
            ]);
        }

        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ["ACCESS DENIED: Status pendaftaran peserta masih {$registration->status->value}."]
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['TICKET EXPIRED: Peserta ini sudah melakukan Check-In sebelumnya!']
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Peserta ini sudah melakukan Check-Out sebelumnya!']
            ]);
        }

        DB::beginTransaction();
        try {
            $registration->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();

            return [
                'user_name' => $registration->user->name,
                'type'      => $type,
                'item_name' => $itemName,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processUserScanCheckIn($user, $token)
    {
        try {
            $payload = json_decode(Crypt::decryptString($token));
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'token' => ['INVALID PROTOCOL: QR Code tidak valid atau rusak.']
            ]);
        }
        
        if (!isset($payload->event_id) || !isset($payload->exp)) {
             throw ValidationException::withMessages([
                'token' => ['INVALID PROTOCOL: Format QR Code tidak sesuai.']
            ]);
        }
    
        if (now()->timestamp > $payload->exp) {
            throw ValidationException::withMessages([
                'token' => ['EXPIRED PROTOCOL: QR Code sudah kadaluarsa. Silakan scan ulang.']
            ]);
        }
    
        $registration = EventRegistration::with('event')
            ->where('user_id', $user->id)
            ->where('event_id', $payload->event_id)
            ->first();
    
        if (!$registration) {
            throw ValidationException::withMessages([
                'status' => ['ACCESS DENIED: Anda belum terdaftar di event ini.']
            ]);
        }
    
        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ["ACCESS DENIED: Status pendaftaran Anda masih {$registration->status->value}."]
            ]);
        }
    
        if ($registration->attended_status === AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['TICKET EXPIRED: Anda sudah melakukan Check-In sebelumnya!']
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Anda sudah melakukan Check-Out sebelumnya!']
            ]);
        }
    
        DB::beginTransaction();
        try {
            $registration->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();
    
            return [
                'event_name' => $registration->event->title,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAttendance(string $id, string $attended)
    {
        $registration = $this->registration->with('user', 'event')->find($id);

        if (!$registration) {
            throw ValidationException::withMessages([
                'id' => ['Pendaftaran tidak ditemukan.']
            ]);
        }

        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ['Hanya pendaftaran dengan status VERIFIED yang dapat diupdate kehadirannya.']
            ]);
        }

        DB::beginTransaction();
        try {
            $status = AttendedStatus::from($attended);
            $checkInAt = $registration->check_in_at;
            $checkOutAt = $registration->check_out_at;

            if ($status === AttendedStatus::CHECKED_IN && !$checkInAt) {
                $checkInAt = now();
            }
            if ($status === AttendedStatus::CHECKED_OUT && !$checkOutAt) {
                $checkOutAt = now();
            }

            $registration->update([
                'attended_status' => $status,
                'check_in_at'     => $checkInAt,
                'check_out_at'    => $checkOutAt,
            ]);
            DB::commit();

            return $registration;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submitEvaluationAndCheckOut($user, string $eventId, array $answers)
    {
        $registration = EventRegistration::with('event')
            ->where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if (!$registration) {
            throw ValidationException::withMessages([
                'status' => ['ACCESS DENIED: Anda belum terdaftar di event ini.']
            ]);
        }

        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ["ACCESS DENIED: Status pendaftaran Anda masih {$registration->status->value}."]
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Anda sudah melakukan Check-Out dan pengisian evaluasi sebelumnya.']
            ]);
        }

        if ($registration->attended_status !== AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['PROTOCOL ERROR: Anda harus melakukan Check-In terlebih dahulu sebelum Check-Out.']
            ]);
        }

        if ($registration->evaluation()->exists()) {
            throw ValidationException::withMessages([
                'evaluation' => ['SESSION TERMINATED: Evaluasi untuk sesi ini sudah pernah disubmit.']
            ]);
        }

        $questions = EvaluationQuestion::where('event_id', $eventId)->get()->keyBy('id');
        $answerMap = collect($answers)->keyBy('question_id');

        // Filter out header questions (headers have no user answers)
        foreach ($questions as $question) {
            if ($question->type === 'header') {
                continue;
            }

            $answer = $answerMap->get($question->id);
            $value = $answer['value'] ?? null;

            if ($question->is_required) {
                if ($value === null || $value === '') {
                    throw ValidationException::withMessages([
                        'answers' => ["Pertanyaan '{$question->question_text}' wajib diisi."]
                    ]);
                }
            }

            if ($value === null || $value === '') {
                continue;
            }

            if ($question->type === QuestionType::RATING && (!is_numeric($value) || (int) $value < 1 || (int) $value > 5)) {
                throw ValidationException::withMessages([
                    'answers' => ["Nilai rating untuk '{$question->question_text}' harus berada di antara 1 sampai 5."]
                ]);
            }

            if ($question->type === QuestionType::MULTIPLE_CHOICE && !in_array($value, $question->options ?? [], true)) {
                throw ValidationException::withMessages([
                    'answers' => ["Pilihan untuk '{$question->question_text}' tidak valid."]
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $evaluation = Evaluation::create([
                'event_registration_id' => $registration->id,
            ]);

            foreach ($answers as $answer) {
                if (!isset($answer['question_id']) || !$questions->has($answer['question_id'])) {
                    continue;
                }

                $questionId = $answer['question_id'];
                $question = $questions->get($questionId);

                // Ignore header type payloads
                if ($question && $question->type === 'header') {
                    continue;
                }

                $value = $answer['value'] ?? null;

                // If frontend didn't send anything for a non-required question, we can skip saving
                if ($value === null || $value === '') {
                    continue;
                }

                EvaluationAnswer::create([
                    'evaluation_id'          => $evaluation->id,
                    'evaluation_question_id' => $questionId,
                    'answer_value'           => is_array($value) ? json_encode($value) : $value,
                ]);
            }

            $registration->update([
                'attended_status' => AttendedStatus::CHECKED_OUT,
                'check_out_at'    => now(),
            ]);

            DB::commit();

            return [
                'event_name' => $registration->event->title,
                'status'     => 'SESSION TERMINATED / CHECK-OUT SUCCESS',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processCheckOutAndEvaluate($user, string $eventId, array $answers)
    {
        return $this->submitEvaluationAndCheckOut($user, $eventId, $answers);
    }
}
