<?php

namespace App\Services;

use App\Enum\StatusRegistration;
use App\Models\Event;
use App\Models\EventTicketRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketRuleService
{
    /**
     * Menghitung batas maksimum tiket untuk User pada Event tertentu
     * berdasarkan aturan dinamis di database.
     */
    public static function calculateMaxTickets(Event $event, ?User $user): int
    {
        $defaultMax = $event->max_tickets_per_user ?? 1;

        if (!$user) {
            return $defaultMax;
        }

        // 1. Lomba Individu / Pendaftar Utama (Cek dari tabel competition_registrations)
        $asMainRegistrantSlugs = DB::table('competition_registrations')
            ->join('competitions', 'competition_registrations.competition_id', '=', 'competitions.id')
            ->where('competition_registrations.user_id', $user->id)
            ->where('competition_registrations.status', StatusRegistration::VERIFIED->value)
            ->pluck('competitions.slug')
            ->toArray();

        // 2. Lomba Kelompok / Anggota Tim (Cek dari tabel competition_members)
        $asMemberSlugs = DB::table('competition_members')
            ->join('competition_registrations', 'competition_members.registration_id', '=', 'competition_registrations.id')
            ->join('competitions', 'competition_registrations.competition_id', '=', 'competitions.id')
            ->where('competition_members.user_id', $user->id)
            ->where('competition_registrations.status', StatusRegistration::VERIFIED->value)
            ->pluck('competitions.slug')
            ->toArray();

        $verifiedCompetitionSlugs = array_unique(array_merge($asMainRegistrantSlugs, $asMemberSlugs));

        if (empty($verifiedCompetitionSlugs)) {
            return $defaultMax;
        }

        // Cari rule dengan angka tiket terbesar berdasarkan kompetisi yang diikuti
        $ruleMax = EventTicketRule::where('event_id', $event->id)
            ->where('condition_type', 'competition')
            ->whereIn('condition_value', $verifiedCompetitionSlugs)
            ->max('max_tickets');

        // Kembalikan nilai yang paling menguntungkan bagi user (maksimal terbesar)
        return max($defaultMax, $ruleMax ?? 0);
    }
}
