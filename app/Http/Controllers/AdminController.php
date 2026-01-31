<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use App\Services\AuthService;
use App\Utils\HttpResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    public function login(LoginRequest $request)
    {
        $result = $this->authService->loginAdmin($request->token);
        $responseData = [
            'token' => $result['token'],
            'admin'  => [
                'name'     => $result['admin']->name,
                'email'    => $result['admin']->email,
                'division' => $result['admin']->division->name ?? null,
            ]
        ];
        $this->success("Login Admin Berhasil", $responseData);
    }
}
