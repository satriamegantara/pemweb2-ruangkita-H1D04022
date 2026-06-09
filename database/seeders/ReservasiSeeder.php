<?php

namespace Database\Seeders;

use App\Models\Reservasi;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservasiSeeder extends Seeder
{
    public function run(): void
    {
        $satria = User::where('email', 'satria@ruangkita.test')->first();
        $pancar = User::where('email', 'pancar@ruangkita.test')->first();
        $yoga = User::where('email', 'yoga@ruangkita.test')->first();
        $hanna = User::where('email', 'hanna@ruangkita.test')->first();

        $labPemrograman = Ruangan::where('nama', 'Lab Pemrograman')->first();
        $labJaringanKomputer = Ruangan::where('nama', 'Lab Jaringan Komputer')->first();
        $ruangE101 = Ruangan::where('nama', 'Ruang E101')->first();
        $ruangF301 = Ruangan::where('nama', 'Ruang F301')->first();
        $ruangC206 = Ruangan::where('nama', 'Ruang C206')->first();

        if (! $satria || ! $labPemrograman) {
            return;
        }

        $reservasi = [
            [
                'ruangan_id' => $labPemrograman->id,
                'user_id' => $satria->id,
                'tanggal' => '2026-06-10',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '10:00:00',
                'status' => 'menunggu',
                'keterangan' => 'Praktikum Pemrograman Web',
            ],
            [
                'ruangan_id' => $labPemrograman->id,
                'user_id' => $pancar->id,
                'tanggal' => '2026-06-10',
                'jam_mulai' => '10:00:00',
                'jam_selesai' => '12:00:00',
                'status' => 'disetujui',
                'keterangan' => 'Kuliah Sistem Operasi',
            ],
            [
                'ruangan_id' => $labPemrograman->id,
                'user_id' => $yoga->id,
                'tanggal' => '2026-06-11',
                'jam_mulai' => '13:00:00',
                'jam_selesai' => '15:00:00',
                'status' => 'disetujui',
                'keterangan' => 'Ujian praktik jaringan',
            ],
            [
                'ruangan_id' => $ruangE101->id,
                'user_id' => $hanna->id,
                'tanggal' => '2026-06-12',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '11:00:00',
                'status' => 'menunggu',
                'keterangan' => 'Seminar tugas akhir',
            ],
            [
                'ruangan_id' => $ruangF301->id,
                'user_id' => $hanna->id,
                'tanggal' => '2026-06-15',
                'jam_mulai' => '14:00:00',
                'jam_selesai' => '16:00:00',
                'status' => 'disetujui',
                'keterangan' => 'Kuliah umum program studi',
            ],
            [
                'ruangan_id' => $labJaringanKomputer->id,
                'user_id' => $yoga->id,
                'tanggal' => '2026-06-09',
                'jam_mulai' => '15:00:00',
                'jam_selesai' => '17:00:00',
                'status' => 'ditolak',
                'keterangan' => 'Latihan konfigurasi router — bentrok dengan jadwal internal',
            ],
            [
                'ruangan_id' => $ruangC206->id,
                'user_id' => $hanna->id,
                'tanggal' => '2026-06-12',
                'jam_mulai' => '13:00:00',
                'jam_selesai' => '15:00:00',
                'status' => 'disetujui',
                'keterangan' => 'Diskusi kelompok proyek',
            ],
        ];

        foreach ($reservasi as $data) {
            Reservasi::updateOrCreate(
                [
                    'ruangan_id' => $data['ruangan_id'],
                    'tanggal' => $data['tanggal'],
                    'jam_mulai' => $data['jam_mulai'],
                ],
                $data,
            );
        }
    }
}
