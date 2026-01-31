<?php

namespace Database\Seeders;

use App\Enum\CompetitionCategory;
use App\Models\Competition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competition = [
            [
                'name'             => 'Fashion Sketch Competition (Intermediate)',
                'slug'             => 'fashion-sketch-intermediate',
                'category'         => CompetitionCategory::INTERMEDIATE,
                'description'      => 'Lomba sketsa untuk pemula.',
                'registration_fee' => 75000,
                'is_active'        => true,
            ],
            [
                'name'             => 'Fashion Sketch Competition (Advanced)',
                'slug'             => 'fashion-sketch-advanced',
                'category'         => CompetitionCategory::ADVANCED,
                'description'      => 'Kompetisi tingkat lanjut Full Collection.',
                'registration_fee' => 100000,
                'is_active'        => true,
            ],
            [
                'name'             => 'Fashion Styling Competition',
                'slug'             => 'fashion-styling-intermediate',
                'category'         => CompetitionCategory::INTERMEDIATE,
                'description'      => 'Mix-and-match tema 90s Retro.',
                'registration_fee' => 50000,
                'is_active'        => true,
            ]
        ];

        foreach($competition as $comp) {
            Competition::create($comp);
        }
    }
}
