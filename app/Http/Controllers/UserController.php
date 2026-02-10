<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getRegistrations(Request $request)
    {
        $userId = $request->user()->id;
        $registrations = $this->userService->getRegistrations($userId);
        return $this->success("Registrasi berhasil diambil", $registrations);
    }
}
