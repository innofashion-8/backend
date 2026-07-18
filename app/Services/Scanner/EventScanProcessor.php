<?php

namespace App\Services\Scanner;

use App\Contracts\ScanProcessorInterface;
use App\Enum\AttendedStatus;
use App\Enum\StatusRegistration;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventScanProcessor implements ScanProcessorInterface
{
    public function processAdminScan(string $id): array
    {
        $registration = EventRegistration::with('user', 'event')->find($id);

        if (!$registration) {
            throw ValidationException::withMessages([
                'registration_id' => ['Data pendaftaran tidak ditemukan di sistem.']
            ]);
        }

        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ["ACCESS DENIED: Status pendaftaran peserta masih {$registration->status->value}."]
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['TICKET EXPIRED: Peserta ini sudah melakukan Check-In sebelumnya!']
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Peserta ini sudah melakukan Check-Out sebelumnya!']
            ]);
        }

        DB::beginTransaction();
        try {
            $registration->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();

            return [
                'user_name' => $registration->user->name,
                'type'      => 'EVENT',
                'item_name' => $registration->event->title,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processUserScan(User $user, object $payload): array
    {
        $registration = EventRegistration::with('event')
            ->where('user_id', $user->id)
            ->where('event_id', $payload->event_id)
            ->first();
    
        if (!$registration) {
            throw ValidationException::withMessages([
                'status' => ['ACCESS DENIED: Anda belum terdaftar di event ini.']
            ]);
        }
    
        if ($registration->status !== StatusRegistration::VERIFIED) {
            throw ValidationException::withMessages([
                'status' => ["ACCESS DENIED: Status pendaftaran Anda masih {$registration->status->value}."]
            ]);
        }
    
        if ($registration->attended_status === AttendedStatus::CHECKED_IN) {
            throw ValidationException::withMessages([
                'attended_status' => ['TICKET EXPIRED: Anda sudah melakukan Check-In sebelumnya!']
            ]);
        }

        if ($registration->attended_status === AttendedStatus::CHECKED_OUT) {
            throw ValidationException::withMessages([
                'attended_status' => ['SESSION TERMINATED: Anda sudah melakukan Check-Out sebelumnya!']
            ]);
        }
    
        DB::beginTransaction();
        try {
            $registration->update([
                'attended_status' => AttendedStatus::CHECKED_IN,
                'check_in_at'     => now(),
            ]);
            DB::commit();
    
            return [
                'event_name' => $registration->event->title,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
