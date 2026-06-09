<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        $ruangan = [
            [
                'nama' => 'Lab Pemrograman',
                'kapasitas' => 20,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '21:00:00',
                'fasilitas' => ['Proyektor', 'AC', '20 Unit PC'],
            ],
            [
                'nama' => 'Lab Jaringan Komputer',
                'kapasitas' => 24,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '21:00:00',
                'fasilitas' => ['Proyektor', 'AC', '24 Unit PC', 'Whiteboard', 'Router', 'Switch'],
            ],
            [
                'nama' => 'Ruang E101',
                'kapasitas' => 50,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '20:00:00',
                'fasilitas' => ['Proyektor', 'AC', 'Papan Tulis'],
            ],
            [
                'nama' => 'Ruang C206',
                'kapasitas' => 50,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '20:00:00',
                'fasilitas' => ['Proyektor', 'AC'],
            ],
            [
                'nama' => 'Ruang F301',
                'kapasitas' => 200,
                'jam_buka' => '08:00:00',
                'jam_tutup' => '22:00:00',
                'fasilitas' => ['Sound System', 'Proyektor', 'AC', 'Panggung'],
            ],
            [
                'nama' => 'Lab Multimedia',
                'kapasitas' => 20,
                'jam_buka' => '07:00:00',
                'jam_tutup' => '21:00:00',
                'fasilitas' => ['Proyektor', 'AC', '20 Unit PC'],
            ],
        ];

        foreach ($ruangan as $data) {
            $fasilitas = $data['fasilitas'];
            unset($data['fasilitas']);

            $ruang = Ruangan::updateOrCreate(
                ['nama' => $data['nama']],
                [
                    ...$data,
                    'foto' => '',
                ],
            );

            foreach ($fasilitas as $namaFasilitas) {
                Fasilitas::updateOrCreate(
                    [
                        'ruangan_id' => $ruang->id,
                        'nama' => $namaFasilitas,
                    ],
                );
            }
        }
    }
}
