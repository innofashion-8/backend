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
                'title'       => 'Talkshow: The Future of Sustainable Fashion',
                'slug'        => 'talkshow-sustainable-fashion',
                'category'    => EventCategory::TALKSHOW,
                'description' => 'Diskusi mendalam masa depan fashion.',
                'price'       => 50000,
                'quota'       => 100,
                'start_time'  => now()->addDays(7)->setHour(10)->setMinute(0),
                'is_active'   => true,
            ],
            [
                'title'       => 'Workshop: Draping Masterclass',
                'slug'        => 'workshop-draping-masterclass',
                'category'    => EventCategory::WORKSHOP, 
                'description' => 'Pelajari teknik draping.',
                'price'       => 150000,
                'quota'       => 30,
                'start_time'  => now()->addDays(8)->setHour(13)->setMinute(0),
                'is_active'   => true,
            ]
        ];

        foreach($events as $event) {
            Event::create($event);
        }
    }
}
