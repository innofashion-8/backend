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
            //BPH
            [
                'name' => 'Cicillia Josephine Setiady',
                'nrp' => 'H14230090',
                'email' => 'h14230090@john.petra.ac.id',
                'division_slug' => 'bph',
            ],
            [
                'name' => 'Benedictus Danaradi',
                'nrp' => 'H14230041',
                'email' => 'h14230041@john.petra.ac.id',
                'division_slug' => 'bph',
            ],
            [
                'name' => 'Sharone Hendrata',
                'nrp' => 'H14230144',
                'email' => 'h14230144@john.petra.ac.id',
                'division_slug' => 'bph',
            ],
            [
                'name' => 'Caroline Paulina Budi',
                'nrp' => 'H14240091',
                'email' => 'h14240091@john.petra.ac.id',
                'division_slug' => 'bph',
            ],
            [
                'name' => 'Kho, Katherine Natalia Hermawan',
                'nrp' => 'H14240136',
                'email' => 'h14240136@john.petra.ac.id',
                'division_slug' => 'bph',
            ],
            // Sekret
            [
                'name' => 'Felice Libelle Jessica',
                'nrp' => 'D11240170',
                'email' => 'd11240170@john.petra.ac.id',
                'division_slug' => 'sekret',
            ],
            [
                'name' => 'Eliana Christabel Irawan',
                'nrp' => 'D11240086',
                'email' => 'd11240086@john.petra.ac.id',
                'division_slug' => 'sekret',
            ],
            [
                'name' => 'Vania Benita',
                'nrp' => 'C14250097',
                'email' => 'c14250097@john.petra.ac.id',
                'division_slug' => 'sekret',
            ],
            [
                'name' => 'Rebecca Tiffany',
                'nrp' => 'D11250013',
                'email' => 'd11250013@john.petra.ac.id',
                'division_slug' => 'sekret',
            ],
            [
                'name' => 'Chelsea Vallerie',
                'nrp' => 'D11250057',
                'email' => 'd11250057@john.petra.ac.id',
                'division_slug' => 'sekret',
            ],
            // Lomba
            [
                'name' => 'Joses Alver Agape',
                'nrp' => 'D11240306',
                'email' => 'd11240306@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            [
                'name' => 'Clara Nadia Adigunawan',
                'nrp' => 'C14240030',
                'email' => 'c14240030@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            [
                'name' => 'Chelsea Tiffany',
                'nrp' => 'C14250019',
                'email' => 'c14250019@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            [
                'name' => 'Sharon Regina',
                'nrp' => 'D11240240',
                'email' => 'd11240240@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            [
                'name' => 'Clayvio Evangelie Chrystie',
                'nrp' => 'H14250019',
                'email' => 'H14250019@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            [
                'name' => 'Jeanette Lovely',
                'nrp' => 'H14250055',
                'email' => 'H14250055@john.petra.ac.id',
                'division_slug' => 'lomba',
            ],
            //IT
            [
                'name' => 'Clarence Evan Wijaya',
                'nrp' => 'C14240069',
                'email' => 'c14240069@john.petra.ac.id',
                'division_slug' => 'it',
            ],
            [
                'name' => 'Ezra Desmond Sutanto',
                'nrp' => 'C14240176',
                'email' => 'c14240176@john.petra.ac.id',
                'division_slug' => 'it',
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
