<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Socialite;

class AuthService 
{
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

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Email anda (' . $googleUser->getEmail() . ') tidak terdaftar sebagai Admin.']
            ]);
        }

        $token = $admin->createToken('ADMIN_TOKEN')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token
        ];
    }
}