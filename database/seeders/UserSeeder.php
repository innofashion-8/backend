<?php

namespace Database\Seeders;

use App\Enum\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'name'        => 'John Petra',
                'email'       => 'c14240069@john.petra.ac.id',
                'password'    => Hash::make('password'),
                'type'        => UserType::INTERNAL,
                'institution' => 'Petra Christian University',
                'phone'       => '081234567890',
                'line'     => 'john.petra',
            ],
            [
                'name'        => 'Jane Ciputra',
                'email'       => 'jane@student.ciputra.ac.id',
                'password'    => Hash::make('password'),
                'type'        => UserType::EXTERNAL,
                'institution' => 'Universitas Ciputra',
                'phone'       => '089876543210',
                'line'     => 'jane_uc',
            ],
            [
                'name'        => 'Budi Highschool',
                'email'       => 'budi@gmail.com',
                'password'    => Hash::make('password'),
                'type'        => UserType::EXTERNAL,
                'institution' => 'SMA Negeri 5 Surabaya',
                'phone'       => '085555555555',
                'line'     => 'budi_sman5',
            ]
        ];

        foreach($user as $us) {
            User::create($us);
        }
    }
}
