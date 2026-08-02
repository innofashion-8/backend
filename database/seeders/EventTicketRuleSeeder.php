<?php

namespace Database\Seeders;

use App\Enum\EventCategory;
use App\Models\Event;
use App\Models\EventTicketRule;
use Illuminate\Database\Seeder;

class EventTicketRuleSeeder extends Seeder
{
    public function run(): void
    {
        $fashionShow = Event::where('category', EventCategory::FASHION_SHOW)->first();

        if (!$fashionShow) {
            $this->command->error('Fashion Show event not found. Skip seeding rules.');
            return;
        }

        // Rule untuk Fashion Sketch participant
        EventTicketRule::firstOrCreate([
            'event_id' => $fashionShow->id,
            'condition_type' => 'competition',
            'condition_value' => 'fashion-sketch',
        ], [
            'max_tickets' => 2,
        ]);

        // Rule untuk Restyling participant
        EventTicketRule::firstOrCreate([
            'event_id' => $fashionShow->id,
            'condition_type' => 'competition',
            'condition_value' => 'restyling',
        ], [
            'max_tickets' => 4,
        ]);

        $this->command->info('Event ticket rules for Fashion Show seeded successfully!');
    }
}
