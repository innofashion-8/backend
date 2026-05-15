<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Competition;
use App\Models\EventRegistration;
use App\Models\CompetitionRegistration;
use App\Enum\StatusRegistration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats()
    {
        $eventStats = [
            'total' => EventRegistration::where('status', '!=', StatusRegistration::DRAFT)->count(),
            'validated' => EventRegistration::where('status', StatusRegistration::VERIFIED)->count(),
            'pending' => EventRegistration::where('status', StatusRegistration::PENDING)->count(),
        ];

        $compStats = [
            'total' => CompetitionRegistration::where('status', '!=', StatusRegistration::DRAFT)->count(),
            'validated' => CompetitionRegistration::where('status', StatusRegistration::VERIFIED)->count(),
            'pending' => CompetitionRegistration::where('status', StatusRegistration::PENDING)->count(),
        ];

        $eventBreakdown = Event::withCount(['eventRegistrations' => function ($query) {
            $query->where('status', '!=', StatusRegistration::DRAFT);
        }])
            ->get()
            ->map(function ($event) {
                return [
                    'label' => $event->title, 
                    'count' => $event->event_registrations_count
                ];
            });

        $compBreakdown = Competition::withCount(['competitionRegistrations' => function ($query) {
            $query->where('status', '!=', StatusRegistration::DRAFT);
        }])
            ->get()
            ->map(function ($comp) {
                return [
                    'label' => $comp->name, // Ngambil kolom 'name' dari tabel competitions
                    'count' => $comp->competition_registrations_count
                ];
            });
        
        $data = [
            'eventStats' => $eventStats,
            'compStats' => $compStats,
            'eventBreakdown' => $eventBreakdown,
            'compBreakdown' => $compBreakdown,
        ];

        return $this->success("Berhasil mengambil data statistik dashboard.", $data);
    }
}