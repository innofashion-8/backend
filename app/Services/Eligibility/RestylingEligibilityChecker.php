<?php

namespace App\Services\Eligibility;

use App\Contracts\EventEligibilityCheckerInterface;
use App\Enum\StatusRegistration;
use App\Models\Event;
use App\Models\User;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionMember;

class RestylingEligibilityChecker implements EventEligibilityCheckerInterface
{
    public function isEligible(Event $event, User $user): bool
    {
        $isLeader = CompetitionRegistration::where('user_id', $user->id)
            ->where('status', StatusRegistration::VERIFIED)
            ->whereHas('competition', function($q) {
                $q->where('name', 'LIKE', '%RESTYLING%')
                  ->orWhere('name', 'LIKE', '%STYLING%');
            })->exists();
            
        if ($isLeader) {
            return true;
        }
        
        // 2. Cek apakah user adalah Anggota (user_id di competition_members)
        $isMember = CompetitionMember::where('user_id', $user->id)
            ->whereHas('registration', function($q) {
                $q->where('status', StatusRegistration::VERIFIED)
                  ->whereHas('competition', function($q2) {
                      $q2->where('name', 'LIKE', '%RESTYLING%')
                         ->orWhere('name', 'LIKE', '%STYLING%');
                  });
            })->exists();
            
        return $isMember;
    }

    public function getErrorMessage(): string
    {
        return "Mohon maaf, pendaftaran event ini eksklusif hanya untuk peserta yang terdaftar pada kompetisi Restyling Innofashion.";
    }
}
