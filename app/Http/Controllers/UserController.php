<?php

namespace App\Http\Controllers;

use App\Data\ProfileDraftDTO;
use App\Http\Requests\User\CompleteRegisterRequest;
use App\Http\Requests\User\DraftRegisterRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $user = $this->userService->getUsers();
        return $this->success("User Data fetched", $user);
    }

    public function exportUsers()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function show($id)
    {
        $user = $this->userService->getUser($id);
        return $this->success("User Data fetched", $user);
    }

    public function getRegistrations(Request $request)
    {
        $userId = $request->user()->id;
        $registrations = $this->userService->getRegistrations($userId);
        return $this->success("Registrasi berhasil diambil", $registrations);
    }
    
    public function saveDraft(DraftRegisterRequest $request)
    {
        $payload = $request->validated()['draft_data'];
        $fileFields = [
            'ktm_path'      => 'ktm/draft', 
            'id_card_path'  => 'id/draft', 
        ];

        $existingDraft = $this->userService->getDraft($request->user()->id);

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

        $dto = new ProfileDraftDTO(
            userId: $request->user()->id,
            draftData: $payload
        );
        
        $user = $this->userService->saveDraft($dto);
        return $this->success("Draft berhasil disimpan", $user);
    }

    public function submitRegister(CompleteRegisterRequest $request)
    {
        $user = $this->userService->completeRegister($request->toDTO());
        return $this->success("Profile berhasil diupdate", $user);
    }

    public function checkStatus(Request $request)
    {
        $user = $request->user();

        $isCompleted = (bool) ($user->is_profile_complete); 

        $draftData = $user->draft_data ?? (object)[];

        return $this->success("Status profile fetched", [
            'is_completed' => $isCompleted,
            'draft_data'   => $draftData,
            'profile_data' => [
                'type'         => $user->type,
                'phone'        => $user->phone, 
                'line'         => $user->line,
                'institution'  => $user->institution,
                'major'        => $user->major,
                'nrp'          => $user->nrp,
                'batch'        => $user->batch,
                'ktm_path'     => $user->ktm_path,
                'id_card_path' => $user->id_card_path,
            ]
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $dto = $request->toDTO($request->user()->id);
        $user = $this->userService->updateProfile($dto);

        return $this->success("Identity recalibrated successfully!", $user);
    }
}
