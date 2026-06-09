<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ruangkita.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nim_nip' => '199103302019031017',
            ],
        );

        $pemohon = [
            [
                'name' => 'Satria Megantara',
                'email' => 'satria@ruangkita.test',
                'nim_nip' => 'H1D024022',
            ],
            [
                'name' => 'Pancar Wahyu',
                'email' => 'pancar@ruangkita.test',
                'nim_nip' => 'H1D024018',
            ],
            [
                'name' => 'Yoga Adi',
                'email' => 'yoga@ruangkita.test',
                'nim_nip' => 'H1D024118',
            ],
            [
                'name' => 'Hanna Aufa',
                'email' => 'hanna@ruangkita.test',
                'nim_nip' => 'H1E023010',
            ],
        ];

        foreach ($pemohon as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'pemohon',
                    'nim_nip' => $data['nim_nip'],
                ],
            );
        }
    }
}
