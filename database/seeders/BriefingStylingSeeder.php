<?php

namespace Database\Seeders;

use App\Enum\EventCategory;
use App\Enum\StatusRegistration;
use App\Models\Competition;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BriefingStylingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $briefingRestyling = Event::where('category', EventCategory::RESTYLING)
                                   ->where('slug', 'briefing-technical-meeting-restyling')
                                   ->first();

        if (!$briefingRestyling) {
            $this->command->error('Event Briefing Restyling not found. Please run EventSeeder first.');
            return;
        }

        $competitionRestyling = Competition::with(['competitionRegistrations.members'])->where('slug', 'restyling')->first();
        
        if (!$competitionRestyling) {
            $this->command->error('Competition Restyling not found.');
            return;
        }

        $competitionRegistrations = $competitionRestyling->competitionRegistrations;

        foreach ($competitionRegistrations as $competitionRegistration) {
            // 1. Masukkan Team Leader-nya dulu (User yang mendaftar)
            EventRegistration::firstOrCreate(
                ['user_id' => $competitionRegistration->user_id, 'event_id' => $briefingRestyling->id],
                [
                    'status' => StatusRegistration::PENDING->value, // Sengaja dibuat PENDING agar bisa trigger email manual via web
                ]
            );

            // 2. Masukkan semua anggotanya
            foreach ($competitionRegistration->members as $member) {
                if ($member->user_id) { // Pastikan user_id member tidak null
                    EventRegistration::firstOrCreate(
                        ['user_id' => $member->user_id, 'event_id' => $briefingRestyling->id],
                        [
                            'status' => StatusRegistration::PENDING->value,
                        ]
                    );
                }
            }
        }
    }
}
