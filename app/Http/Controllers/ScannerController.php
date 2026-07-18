<?php

namespace App\Http\Controllers;

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
            'type' => 'nullable|string|in:event,ticket'
        ]);

        if ($request->filled('token')) {
            try {
                $payload = json_decode(Crypt::decryptString($request->token));
                $id = $payload->id ?? null;
                $type = $payload->type ?? null;
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'token' => ['INVALID PROTOCOL: QR Code tidak valid atau rusak.']
                ]);
            }
            if (!$id || !$type) {
                throw ValidationException::withMessages([
                    'token' => ['INVALID PROTOCOL: Format QR Code tidak lengkap.']
                ]);
            }
        } else {
            $id = $request->id;
            $type = $request->type;
        }

        if (!$id || !$type) {
            throw ValidationException::withMessages([
                'id' => ['ID dan Type wajib diisi jika tidak menggunakan token.']
            ]);
        }

        $processor = ScannerFactory::make($type);
        $data = $processor->processAdminScan($id);

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
        
        if (!isset($payload->type) || !isset($payload->exp)) {
             throw ValidationException::withMessages([
                'token' => ['INVALID PROTOCOL: Format QR Code tidak sesuai. Atribut type dan exp wajib ada.']
            ]);
        }
    
        if (now()->timestamp > $payload->exp) {
            throw ValidationException::withMessages([
                'token' => ['EXPIRED PROTOCOL: QR Code sudah kadaluarsa. Silakan scan ulang.']
            ]);
        }

        $processor = ScannerFactory::make($payload->type);
        $result = $processor->processUserScan($request->user(), $payload);

        return $this->success("ACCESS GRANTED. Kehadiran Anda telah dicatat untuk acara {$result['event_name']}.", $result);
    }
}
