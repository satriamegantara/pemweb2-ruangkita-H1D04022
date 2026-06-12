# RuangKita

Aplikasi reservasi ruangan berbasis web yang dibangun dengan **Laravel 13** dan **Livewire 4**.
RuangKita memudahkan pemohon (dosen/mahasiswa) untuk mengajukan permohonan reservasi ruangan
dan admin untuk mengelola persetujuan, ruangan, serta fasilitas.

---

## Fitur Utama

### Untuk Pemohon

- **Registrasi & Login** — membuat akun dengan peran otomatis `pemohon`
- **Buat Reservasi** — pilih ruangan, tanggal, dan jam; validasi bentrok realtime
- **Slot Tersedia** (Livewire Island) — panel yang menampilkan slot kosong vs terisi
- **Riwayat Reservasi** — lihat status reservasi (menunggu/disetujui/ditolak), filter, search, dan batalkan
- **Jadwal Harian** — lihat ketersediaan ruangan per jam untuk tanggal tertentu

### Untuk Admin

- **Manajemen Ruangan** — CRUD ruangan dengan search, filter kapasitas, pagination, dan upload foto
- **Manajemen Fasilitas** — kelola daftar fasilitas per ruangan
- **Manajemen Pemohon** — lihat daftar akun pemohon
- **Persetujuan Reservasi** — setujui/tolak permintaan reservasi dengan re-check bentrok
- **Jadwal Harian** — lihat jadwal ruangan dengan grid per jam

### Fitur Umum

- Notifikasi toast global (Livewire dispatch)
- Middleware autentikasi & role (admin/pemohon)
- Validasi realtime dengan `wire:model.live`
- Desain responsif dengan Tailwind CSS 4

---

## Kebutuhan Sistem

| Komponen      | Versi Minimum                                               |
| ------------- | ----------------------------------------------------------- |
| PHP           | 8.3+                                                        |
| Composer      | 2.x                                                         |
| Node.js & npm | 18+                                                         |
| Database      | MySQL 8 / MariaDB 10.6+ (atau SQLite untuk development)     |
| Web Server    | Apache / Nginx (atau `php artisan serve` untuk development) |

**Ekstensi PHP yang dibutuhkan:**

- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

## Langkah Instalasi

Ikuti semua langkah berikut secara berurutan agar aplikasi dapat dijalankan.

### Langkah 1 — Clone Repository

```bash
git clone <url-repository-ini>
cd ruangkita
```

### Langkah 2 — Install Dependency PHP

```bash
composer install
```

### Langkah 3 — Install Dependency Frontend

```bash
npm install
```

### Langkah 4 — Salin dan Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Kemudian buka file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ruangkita
DB_USERNAME=root
DB_PASSWORD=
```

**Pastikan:**

- MySQL/MariaDB sudah berjalan
- Database `ruangkita` sudah dibuat (atau biarkan kosong, migrasi akan otomatis membuat database jika `DB_DATABASE` tidak ditemukan)

### Langkah 5 — Migrasi dan Seed Database

```bash
php artisan migrate --seed
```

Perintah ini akan:

1. Membuat semua tabel: `users`, `cache`, `jobs`, `ruangan`, `fasilitas`, `reservasi`
2. Menambahkan data awal: 1 admin, 4 pemohon, 5+ ruangan dengan fasilitas, dan beberapa contoh reservasi

### Langkah 6 — Buat Symbolic Link Storage

```bash
php artisan storage:link
```

### Langkah 7 — Jalankan Development Server

Buka terminal di folder `ruangkita` dan jalankan:

```bash
php artisan serve
```

Kemudian buka terminal baru (atau tab baru) dan jalankan:

```bash
npm run dev
```

Buka browser ke [http://localhost:8000](http://localhost:8000).

**Catatan:** Untuk production, ganti `npm run dev` dengan `npm run build` untuk kompilasi aset.

---

## Akun Demo

Data berikut otomatis dibuat saat menjalankan `php artisan migrate --seed`. Password untuk semua akun: **`password`**

| Role    | Nama             | Email                   | NIM/NIP              |
| ------- | ---------------- | ----------------------- | -------------------- |
| Admin   | Administrator    | `admin@ruangkita.test`  | `199103302019031017` |
| Pemohon | Satria Megantara | `satria@ruangkita.test` | `H1D024022`          |
| Pemohon | Pancar Wahyu     | `pancar@ruangkita.test` | `H1D024018`          |
| Pemohon | Yoga Adi         | `yoga@ruangkita.test`   | `H1D024118`          |
| Pemohon | Hanna Aufa       | `hanna@ruangkita.test`  | `H1E023010`          |

---

## Cara Menggunakan

### Sebagai Admin

1. Buka [http://localhost:8000](http://localhost:8000)
2. Klik **Login** dan masuk dengan `admin@ruangkita.test` / `password`
3. Di menu admin, Anda dapat:
    - **Kelola Ruangan** — tambah/edit ruangan, upload foto, kelola fasilitas
    - **Kelola Pemohon** — lihat daftar semua pemohon
    - **Persetujuan Reservasi** — setujui atau tolak reservasi yang masuk

### Sebagai Pemohon

1. Klik **Daftar** atau **Login** (jika belum punya akun)
2. Isi form registrasi atau masuk dengan akun pemohon
3. Klik **Buat Reservasi** → pilih ruangan, tanggal, jam mulai-selesai
4. Cek panel **Slot Tersedia** di sebelah kanan untuk melihat jam yang kosong
5. Klik **Simpan Reservasi** — status awal: `menunggu`
6. Cek **Daftar Reservasi** untuk melihat status dan membatalkan

---

## Struktur Proyek Singkat

```
app/
  Models/          Eloquent models (User, Ruangan, Fasilitas, Reservasi)
  Rules/           Custom validation (NoJadwalBentrok)
  Http/Middleware/ EnsureUserIsAdmin
database/
  migrations/      Skema tabel
  seeders/         Data awal (User, Ruangan, Reservasi)
resources/views/
  layouts/         Layout utama (navbar, footer)
  pages/           Halaman Livewire SFC (landing, auth, admin, reservasi, jadwal)
  components/      Komponen reusable (toast, slot-tersedia Island)
routes/
  web.php          Route aplikasi
```

---

## Teknologi yang Digunakan

| Teknologi     | Versi | Fungsi                                                    |
| ------------- | ----- | --------------------------------------------------------- |
| Laravel       | 13    | Backend framework                                         |
| Livewire      | 4     | Frontend SPA tanpa JavaScript manual (SFC, Island, Slots) |
| Tailwind CSS  | 4     | Styling                                                   |
| MySQL/MariaDB | 8+    | Database                                                  |
| PHP           | 8.3+  | Runtime                                                   |
| Vite          | 5     | Asset bundling                                            |

---

## Lisensi

Proyek ini dibuat untuk tugas kuliah **Pemrograman Web II**.
