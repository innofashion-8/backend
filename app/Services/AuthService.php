<?php

namespace App\Services;

use App\Data\RegisterDTO;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthService 
{
    protected $admin;
    protected $user;

    public function __construct(Admin $admin, User $user)
    {
        $this->admin = $admin;
        $this->user = $user;
    }

    public function register(RegisterDTO $data) 
    {
        $user = User::create([
            'name'        => $data->name,
            'email'       => $data->email,
            'password'    => Hash::make($data->password),
            'type'        => $data->type,
            'institution' => $data->institution,
            'phone'       => $data->phone,
            'line_id'     => $data->line,
        ]);

        $token = $user->createToken('USER_TOKEN')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token
        ];
    }

    public function login(string $email, string $password)
    {
        $user = $this->user->where('email', $email)->first();
        if (!$user)
        {
            throw ValidationException::withMessages([
                'email' => ['Invalid Credentials']
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Invalid Credentials']
            ]);
        }

        $token = $user->createToken('USER_TOKEN', ['*'], now()->addDay())->plainTextToken;

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    public function googleLogin(string $googleToken)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($googleToken);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'token' => ['Token Google tidak valid atau kadaluwarsa.']
            ]);
        }

        $user = $this->user->where('email', $googleUser->getEmail())->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email anda (' . $googleUser->getEmail() . ') tidak terdaftar.']
            ]);
        }

        $token = $user->createToken('USER_TOKEN', ['*'], now()->addDay())->plainTextToken;

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    public function loginAdmin(string $googleToken)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($googleToken);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'token' => ['Token Google tidak valid atau kadaluwarsa.']
            ]);
        }
        $email = $googleUser->getEmail();

        if (!Str::endsWith($email, '@john.petra.ac.id')) {
            throw ValidationException::withMessages([
                'email' => ['Login gagal. Harap gunakan email @john.petra.ac.id.']
            ]);
        }

        $admin = $this->admin::where('email', $email)->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Email anda (' . $googleUser->getEmail() . ') tidak terdaftar sebagai Admin.']
            ]);
        }

        $admin->syncRoleByDivision();

        $token = $admin->createToken('ADMIN_TOKEN', ['*'], now()->addDay())->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
        return true;
    }
}