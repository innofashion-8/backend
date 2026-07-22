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
            [
                'title'       => 'Briefing & Technical Meeting Restyling',
                'slug'        => 'briefing-technical-meeting-restyling',
                'category'    => EventCategory::RESTYLING,
                'description' => 'Sebelum hari pelaksanaan, seluruh peserta wajib mengikuti sesi briefing pra-lomba secara online. Di sesi ini, peserta akan mendapat penjelasan lengkap seputar tema, ketentuan pengerjaan, hingga teknis pelaksanaan lomba, supaya semua tim siap tampil maksimal di hari-H.',
                'price'       => 0,
                'quota'       => 20,
                'start_time'  => '2026-08-01 10:30:00',
                'venue'       => 'Online',
                'wa_link'     => null,
                'max_tickets_per_user' => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'The D-Day Restyling',
                'slug'        => 'the-d-day-restyling',
                'category'    => EventCategory::RESTYLING,
                'description' => 'Hari di mana para desainer muda unjuk kemampuan! Setiap tim akan mengolah sarung batik menjadi busana bersiluet asimetris dalam waktu terbatas, lalu mempresentasikan konsep dan cerita di balik karyanya di hadapan dewan juri.',
                'price'       => 0,
                'quota'       => 20,
                'start_time'  => '2026-08-14 10:00:00',
                'venue'       => 'Ciputra World Surabaya',
                'wa_link'     => null,
                'max_tickets_per_user' => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Fashion Show & Awarding Night',
                'slug'        => 'fashion-show-awarding-night',
                'category'    => EventCategory::FASHION_SHOW, 
                'description' => 'Puncak acara! 6 tim terbaik hasil seleksi akan naik panggung memperagakan karya mereka dalam sesi fashion show, dilanjutkan dengan malam penganugerahan juara dengan total hadiah Rp25.000.000 (belum dipotong pajak).',
                'price'       => 0,
                'quota'       => 450,
                'start_time'  => '2026-08-14 17:45:00',
                'venue'       => 'Ciputra World Surabaya',
                'wa_link'     => null,
                'max_tickets_per_user' => 5,
                'is_active'   => true,
            ],
        ];

        foreach($events as $event) {
            Event::updateOrCreate(
                ['slug' => $event['slug']], 
                $event
            );
        }
    }
}
