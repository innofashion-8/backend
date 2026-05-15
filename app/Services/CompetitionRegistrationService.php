<?php

namespace App\Services;

use App\Data\CompetitionFilterDTO;
use App\Data\SaveDraftDTO;
use App\Data\SubmitCompetitionDTO;
use App\Data\UpdateStatusDTO;
use App\Data\UploadSubmissionDTO;
use App\Enum\CompetitionCategory;
use App\Enum\FileType;
use App\Enum\ParticipantType;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Mail\RegistrationRejected;
use App\Mail\RegistrationVerified;
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
            ->with(['user', 'competition', 'members.user', 'submissions'])
            ->where('status', '!=', StatusRegistration::DRAFT)
            ->latest();

        if ($filters->competitionId) {
            $query->where('competition_id', $filters->competitionId);
        }

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->competitionName) {
            $compName = $filters->competitionName;
            $query->whereHas('competition', function (Builder $q) use ($compName) {
                $q->where('name', $compName);
            });
        }

        if ($filters->category) {
            $query->where('category', $filters->category);
        }

        if ($filters->userType) {
            $userType = $filters->userType;
            $query->whereHas('user', function (Builder $q) use ($userType) {
                $q->where('type', $userType);
            });
        }

        if ($filters->search) {
            $search = $filters->search;
            
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function (Builder $userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nrp', 'like', "%{$search}%");
                })
                ->orWhereHas('competition', function (Builder $compQ) use ($search) {
                    $compQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters->perPage)->withQueryString();
    }

    public function saveDraft(SaveDraftDTO $dto): CompetitionRegistration
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('competition_id', $dto->activityId)
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
                'competition_id' => $dto->activityId,
            ],
            [
                'draft_data' => $dto->draftData,
                'status' => $statusToSave,
            ]
        );
    }
    
    public function getDraft(string $userId, string $competitionId): ?CompetitionRegistration
    {
        return $this->registration->with(['members.user', 'submissions'])
            ->where('user_id', $userId)
            ->where('competition_id', $competitionId)
            ->first();
    }

    public function submitFinal(SubmitCompetitionDTO $dto): CompetitionRegistration
    {
        // 1. Pengecekan pendaftaran lama
        $registration = $this->registration->with('user')
            ->where('user_id', $dto->userId)
            ->where('competition_id', $dto->competitionId)
            ->first();

        if ($registration && !in_array($registration->status, [StatusRegistration::DRAFT, StatusRegistration::REJECTED])) {
            throw ValidationException::withMessages([
                'status' => ['Anda sudah terdaftar di kompetisi ini. Pendaftaran sedang diproses atau sudah diverifikasi.']
            ]);
        }

        $draft = $registration ? ($registration->draft_data ?? []) : [];
        $draftFilesMoved = [];

        if (!empty($dto->membersData)) {
            $seenPhones = [];
            foreach ($dto->membersData as $index => $memberData) {
                $phone = $memberData['phone'];
                if (in_array($phone, $seenPhones)) {
                    throw ValidationException::withMessages([
                        "members.{$index}.phone" => ["Nomor WhatsApp ini sudah digunakan oleh anggota lain dalam tim."]
                    ]);
                }
                $seenPhones[] = $phone;
            }
        }

        DB::beginTransaction();

        try {
            $competition = Competition::findOrFail($dto->competitionId);

            $totalMembers = 1 + count($dto->membersData);

            if ($totalMembers < $competition->min_members || $totalMembers > $competition->max_members) {
                throw ValidationException::withMessages([
                    'members' => ["Jumlah anggota tim (termasuk ketua) harus antara {$competition->min_members} sampai {$competition->max_members} orang."]
                ]);
            }

            $categoryToSave = $dto->category?->value;
            $groupNameToSave = $dto->groupName;

            if ($competition->participant_type === ParticipantType::GROUP->value) {
                $categoryToSave = CompetitionCategory::INTERMEDIATE->value;

                if (empty($groupNameToSave)) {
                    throw ValidationException::withMessages([
                        'group_name' => ['Nama grup wajib diisi untuk pendaftaran kelompok.']
                    ]);
                }
            } else {
                $groupNameToSave = null;

                if (empty($categoryToSave)) {
                    throw ValidationException::withMessages([
                        'category' => ['Kategori lomba (Intermediate / Advanced) wajib dipilih.']
                    ]);
                }
            }

            $dataToSave = [
                'status'     => StatusRegistration::PENDING,
                'region'     => $dto->region->value,
                'category'   => $categoryToSave,
                'group_name' => $groupNameToSave,
                'draft_data' => null,
            ];

            // 4. Simpan / Update Pendaftaran
            if ($registration) {
                $registration->update($dataToSave);
                $registration->members()->delete();
            } else {
                $dataToSave['user_id']        = $dto->userId;
                $dataToSave['competition_id'] = $dto->competitionId;
                $registration = $this->registration->create($dataToSave);
            }

            // 5. Masukkan Ketua (Leader)
            $registration->members()->create([
                'user_id'      => $dto->userId,
                'member_order' => 1
            ]);

            // 6. Proses Anggota Tim
            $memberOrder = 2;
            foreach ($dto->membersData as $index => $memberData) {
                $email      = $memberData['email'];
                $idCardPath = $dto->memberFiles[$email] ?? null;

                // Penyelamatan file dari Draft
                if (!$idCardPath && isset($draft['members'][$index]['id_card'])) {
                    $draftPath = $draft['members'][$index]['id_card'];
                    if (Storage::disk('public')->exists($draftPath)) {
                        $filename   = basename($draftPath);
                        $idCardPath = "id_cards/{$filename}";
                        Storage::disk('public')->move($draftPath, $idCardPath);
                        $draftFilesMoved[] = $draftPath;
                    }
                }

                // Wajibkan file KTP
                if (!$idCardPath) {
                    throw ValidationException::withMessages([
                        "members.{$index}.id_card" => ["File KTP/Kartu pelajar untuk anggota {$email} wajib dilampirkan."]
                    ]);
                }

                $phoneExists = User::where('phone', $memberData['phone'])
                    ->where('email', '!=', $email)
                    ->exists();

                if ($phoneExists) {
                    throw ValidationException::withMessages([
                        "members.{$index}.phone" => ["Nomor WhatsApp {$memberData['phone']} sudah terdaftar oleh pengguna lain."]
                    ]);
                }

                // Buat atau Cari User Anggota
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'                => $memberData['name'],
                        'phone'               => $memberData['phone'],
                        'institution'         => $registration->user->institution,
                        'type'                => $registration->user->type,
                        'id_card_path'        => $idCardPath,
                        'is_profile_complete' => false,
                    ]
                );

                if ($user->wasRecentlyCreated === false) {
                    $updateData = ['id_card_path' => $idCardPath];
                    // Update phone hanya kalau user belum punya phone (hindari overwrite data existing)
                    if (empty($user->phone)) {
                        $updateData['phone'] = $memberData['phone'];
                    }
                    $user->update($updateData);
                }

                $registration->members()->create([
                    'user_id'      => $user->id,
                    'member_order' => $memberOrder
                ]);

                $memberOrder++;
            }

            // 7. Bersihkan sisa file draft lama
            if (isset($draft['members']) && is_array($draft['members'])) {
                foreach ($draft['members'] as $draftMember) {
                    if (isset($draftMember['id_card'])) {
                        $oldDraftPath = $draftMember['id_card'];
                        if (!in_array($oldDraftPath, $draftFilesMoved) && Storage::disk('public')->exists($oldDraftPath)) {
                            Storage::disk('public')->delete($oldDraftPath);
                        }
                    }
                }
            }

            DB::commit();

            return $registration;

        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($dto->memberFiles as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            foreach ($draftFilesMoved as $movedDraftPath) {
                $filename  = basename($movedDraftPath);
                $finalPath = "id_cards/{$filename}";
                if (Storage::disk('public')->exists($finalPath)) {
                    Storage::disk('public')->move($finalPath, $movedDraftPath);
                }
            }

            throw $e;
        }
    }

    public function uploadSubmission(UploadSubmissionDTO $dto)
    {
        $registration = $this->registration->where('user_id', $dto->userId)
            ->where('competition_id', $dto->competitionId)
            ->first();

        if (!$registration) {
            Storage::disk('public')->delete([$dto->artworkPath, $dto->conceptPath]);
            throw ValidationException::withMessages(['status' => ['Pendaftaran tidak ditemukan.']]);
        }

        if ($registration->status !== StatusRegistration::VERIFIED) {
            Storage::disk('public')->delete([$dto->artworkPath, $dto->conceptPath]);
            throw ValidationException::withMessages([
                'status' => ['Pendaftaran Anda belum divalidasi oleh admin. Tidak dapat mengumpulkan karya saat ini.']
            ]);
        }

        DB::beginTransaction();

        try {
            $existingSubmissions = $registration->submissions;
            foreach ($existingSubmissions as $sub) {
                if (Storage::disk('public')->exists($sub->file_path)) {
                    Storage::disk('public')->delete($sub->file_path);
                }
                $sub->delete();
            }

            $registration->submissions()->create([
                'file_type' => FileType::ARTWORK->value,
                'file_path' => $dto->artworkPath,
            ]);

            $registration->submissions()->create([
                'file_type' => FileType::CONCEPT->value,
                'file_path' => $dto->conceptPath,
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Storage::disk('public')->delete([$dto->artworkPath, $dto->conceptPath]);
            
            Log::error("Error Upload Submission Lomba: " . $e->getMessage());
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
}