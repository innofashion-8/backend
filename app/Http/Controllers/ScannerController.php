<?php

namespace App\Http\Controllers;

use App\Services\Attendance\AttendanceManagerFactory;
use App\Services\Scanner\ScannerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class ScannerController extends Controller
{
    /**
     * Digunakan oleh panitia untuk scan QR code user/guest.
     * Payload langsung dikirim dari frontend berupa id dan type.
     */
    public function adminCheckIn(Request $request)
    {
        $request->validate([
            'token' => 'nullable|string',
            'id' => 'nullable|string',
        ]);

        $code = null;
        if ($request->filled('token')) {
            try {
                $payload = json_decode(Crypt::decryptString($request->token));
                $code = $payload->id ?? null;
            } catch (\Exception $e) {
                $code = $request->token;
            }
        } else {
            $code = $request->id;
        }

        if (!$code) {
            throw ValidationException::withMessages([
                'id' => ['ID atau Token wajib diisi.']
            ]);
        }

        $manager = AttendanceManagerFactory::makeFromTicketCode($code);
        $data = $manager->checkIn($code);

        $message = "ACCESS GRANTED. Selamat datang, {$data['user_name']} di {$data['type']} {$data['item_name']}.";

        return $this->success($message, $data);
    }

    public function userScanCheckIn(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        try {
            $payload = json_decode(Crypt::decryptString($request->token));
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'token' => ['INVALID PROTOCOL: QR Code tidak valid atau rusak.']
            ]);
        }
        
        if (!isset($payload->type) || !property_exists($payload, 'exp')) {
             throw ValidationException::withMessages([
                'token' => ['INVALID PROTOCOL: Format QR Code tidak sesuai. Atribut type dan exp wajib ada.']
            ]);
        }
    
        if ($payload->exp > 0 && now()->timestamp > $payload->exp) {
            throw ValidationException::withMessages([
                'token' => ['EXPIRED PROTOCOL: QR Code sudah kadaluarsa. Silakan scan ulang.']
            ]);
        }

        $processor = ScannerFactory::make($payload->type);
        $result = $processor->processUserScan($request->user(), $payload);

        return $this->success("ACCESS GRANTED. Kehadiran Anda telah dicatat untuk acara {$result['event_name']}.", $result);
    }
}
