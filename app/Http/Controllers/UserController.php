<?php

namespace App\Http\Controllers;

use App\Data\ProfileDraftDTO;
use App\Http\Requests\User\CompleteProfileRequest;
use App\Http\Requests\User\DraftProfileRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    
    public function saveDraft(DraftProfileRequest $request)
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

    public function submitProfile(CompleteProfileRequest $request)
    {
        $user = $this->userService->completeProfile($request->toDTO());
        return $this->success("Profile berhasil diupdate", $user);
    }
}
