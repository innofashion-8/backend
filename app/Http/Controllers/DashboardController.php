<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Competition;
use App\Models\EventRegistration;
use App\Models\CompetitionRegistration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats()
    {
        $eventStats = [
            'total' => EventRegistration::count(),
            'validated' => EventRegistration::where('status', 'verified')->count(),
            'pending' => EventRegistration::where('status', 'pending')->count(),
        ];

        $compStats = [
            'total' => CompetitionRegistration::count(),
            'validated' => CompetitionRegistration::where('status', 'verified')->count(),
            'pending' => CompetitionRegistration::where('status', 'pending')->count(),
        ];

        $eventBreakdown = Event::withCount('eventRegistrations')
            ->get()
            ->map(function ($event) {
                return [
                    'label' => $event->title, 
                    'count' => $event->event_registrations_count
                ];
            });

        $compBreakdown = Competition::withCount('competitionRegistrations')
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