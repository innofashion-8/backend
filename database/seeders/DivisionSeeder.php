<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Badan Pengurus Harian',
                'slug' => 'bph',
            ],
            [
                'name' => 'Kepala Bidang',
                'slug' => 'kabid',
            ],
            [
                'name' => 'Transportasi dan Keamanan',
                'slug' => 'transkapman',
            ],
            [
                'name' => 'Sekretariat',
                'slug' => 'sekret',
            ],
            [
                'name' => 'Konsumsi Kesehatan',
                'slug' => 'konkes',
            ],
            [
                'name' => 'Lomba',
                'slug' => 'lomba',
            ],
            [
                'name' => 'Pameran',
                'slug' => 'pameran',
            ],
            [
                'name' => 'Acara',
                'slug' => 'acara',
            ],
            [
                'name' => 'Information Technology',
                'slug' => 'it',
            ],
            [
                'name' => 'Sponsorship',
                'slug' => 'sponsor',
            ],
            [
                'name' => 'Public Relation',
                'slug' => 'pr',
            ],
            [
                'name' => 'Creative',
                'slug' => 'creative',
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
