<?php

namespace Database\Seeders;

use App\Enum\ParticipantType;
use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitions = [
            [
                'name'                  => 'Fashion Sketch',
                'slug'                  => 'fashion-sketch',
                'participant_type'      => ParticipantType::INDIVIDUAL->value,
                'min_members'           => 1,
                'max_members'           => 1,
                'wa_link_national'      => 'https://chat.whatsapp.com/DgvcobX4nYMGPQhKpvvAcH',
                'wa_link_international' => 'https://chat.whatsapp.com/CYwpVZVJYwFIAIScHHj2KU',
                'description'           => 'Lomba fashion sketch bersifat individu. Dibagi menjadi 2 kategori: Intermediate (SMP-SMA) dan Advanced (Mahasiswa/Umum). Peserta wajib mengumpulkan karya sketch dan dokumen konsep.',
                'is_active'             => true,
                
                // Timeline dari brief: 23 Maret - 10 Juli 2026
                'registration_open_at'  => Carbon::parse('2026-03-23 00:00:00'),
                'registration_close_at' => Carbon::parse('2026-07-10 23:59:59'),
                
                // Pengerjaan dan Pengumpulan Karya: 23 Maret - 12 Juli 2026
                'submission_open_at'    => Carbon::parse('2026-03-23 00:00:00'),
                'submission_close_at'   => Carbon::parse('2026-07-12 23:59:59'),
            ],

            [
                'name'                  => 'Restyling',
                'slug'                  => 'restyling',
                'participant_type'      => ParticipantType::GROUP->value,
                'min_members'           => 2,
                'max_members'           => 3, // Sesuai brief: 2-3 anggota
                'wa_link_national'      => 'https://chat.whatsapp.com/DVRPtov9lUxCKV9Kre08J8',
                'wa_link_international' => 'https://chat.whatsapp.com/FLQMOjegOjN81OyYVXFlSR',
                'description'           => 'Lomba fashion sketch bersifat kelompok (2-3 orang) dan harus berasal dari sekolah yang sama. Hanya dapat diikuti oleh siswa-siswi SMP/SMA.',
                'is_active'             => true,
                
                // Timeline dari brief: 23 Maret - 30 Juli 2026
                'registration_open_at'  => Carbon::parse('2026-03-23 00:00:00'),
                'registration_close_at' => Carbon::parse('2026-07-30 23:59:59'),
                
                // Karena di brief Restyling gak nyebutin tgl submission terpisah, 
                // kita samain aja sama tanggal close regis
                'submission_open_at'    => Carbon::parse('2026-03-23 00:00:00'),
                'submission_close_at'   => Carbon::parse('2026-07-30 23:59:59'),
            ]
        ];

        foreach($competitions as $comp) {
            Competition::create($comp);
        }
    }
}