<?php

namespace Database\Seeders;

use App\Enum\EventCategory;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title'       => 'SEMINAR: What Makes It Fashion',
                'slug'        => 'seminar-what-makes-it-fashion',
                'category'    => EventCategory::TALKSHOW,
                'description' => 'Diskusi mendalam masa depan fashion.',
                'price'       => 50000,
                'quota'       => 100,
                'wa_link'     => 'https://wa.me/6281234567890',
                'start_time'  => now()->addDays(7)->setHour(10)->setMinute(0),
                'is_active'   => true,
            ],
            [
                'title'       => 'Workshop: Pretty Little Daredevil',
                'slug'        => 'workshop-pretty-little-daredevil',
                'category'    => EventCategory::WORKSHOP, 
                'description' => 'Pelajari teknik draping.',
                'price'       => 150000,
                'quota'       => 30,
                'wa_link'     => 'https://wa.me/6281234567890',
                'start_time'  => now()->addDays(8)->setHour(13)->setMinute(0),
                'is_active'   => true,
            ],
        ];

        foreach($events as $event) {
            Event::create($event);
        }
    }
}
