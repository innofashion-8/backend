<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Division;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all()->keyBy('slug');
        $adminsData = [
            [
                'name' => 'Clarence Evan Wijaya',
                'nrp' => 'C14240069',
                'email' => 'c14240069@john.petra.ac.id',
                'division_slug' => 'it',
            ],
            [
                'name' => 'Clarence Evan Wijaya',
                'nrp' => 'C14240000',
                'email' => 'clarenceevan0907@gmail.com',
                'division_slug' => 'sekret',
            ],
        ];

        foreach ($adminsData as $adminData) {
            if (isset($divisions[$adminData['division_slug']])) {
                Admin::create([
                    'name' => $adminData['name'],
                    'nrp' => $adminData['nrp'],
                    'email' => $adminData['email'],
                    'division_id' => $divisions[$adminData['division_slug']]->id,
                ]);
            }
        }

    }
}
