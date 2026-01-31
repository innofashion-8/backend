<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\GoogleLoginRequest;
use App\Http\Requests\User\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->email, $request->password);
        $responseData = [
            'token' => $result['token'],
            'user'  => $result['user']
        ];
        return $this->success("Login Berhasil", $responseData);
    }

    public function googleLogin(GoogleLoginRequest $request)
    {
        $result = $this->authService->googleLogin($request->token);
        $responseData = [
            'token' => $result['token'],
            'user'  => $result['user']
        ];
        return $this->success("Login Berhasil", $responseData);
    }
    
    public function loginAdmin(GoogleLoginRequest $request)
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
        return $this->success("Login Admin Berhasil", $responseData);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        
        return $this->success("Logout Berhasil", null);
    }
}
