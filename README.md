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
- **Manajemen Ruangan** — CRUD ruangan dengan search, filter kapasitas, pagination
- **Upload Foto Ruangan** — kelola foto ruangan
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

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js & npm | 18+ |
| Database | MySQL 8 / MariaDB 10.6+ (atau SQLite untuk development) |
| Web Server | Apache / Nginx (atau `php artisan serve` untuk development) |

**Ekstensi PHP yang dibutuhkan:**
- BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

## Langkah Instalasi

### 1. Clone Repository

```bash
git clone <url-repository-ini>
cd ruangkita
```

### 2. Install Dependency

```bash
composer install
npm install
```

### 3. Salin File Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan bagian berikut:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ruangkita
DB_USERNAME=root
DB_PASSWORD=
```

Buat database baru dengan nama yang sesuai di MySQL/MariaDB.

### 5. Migrasi & Seed Database

```bash
php artisan migrate --seed
```

Seeder akan menambahkan akun admin, beberapa pemohon, ruangan, dan data reservasi contoh.

**Akun admin default** (dari seeder):
- Email: `admin@ruangkita.test`
- Password: `password`

### 6. Storage Link

```bash
php artisan storage:link
```

Perintah ini membuat symbolic link dari `public/storage` ke `storage/app/public` agar foto ruangan dapat diakses melalui URL.

### 7. Build Aset Frontend

Untuk development:
```bash
npm run dev
```

Untuk production:
```bash
npm run build
```

### 8. Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser ke [http://localhost:8000](http://localhost:8000).

---

## Struktur Proyek Singkat

```
app/
  Models/          Eloquent models (User, Ruangan, Fasilitas, Reservasi)
  Rules/           Custom validation rules (NoJadwalBentrok)
  Http/Middleware/ Middleware (EnsureUserIsAdmin)
database/
  migrations/      Skema tabel
  seeders/         Data awal
resources/views/
  layouts/         Layout utama aplikasi
  pages/           Halaman (landing, auth, admin/*, reservasi/*, jadwal/*)
  components/      Komponen reusable (toast, slot-tersedia Island)
routes/
  web.php          Definisi route aplikasi
```

---

## Akun Demo (Setelah Seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@ruangkita.test` | `password` |
| Pemohon | `budi@ruangkita.test` | `password` |
| Pemohon | `siti@ruangkita.test` | `password` |

---

## Lisensi

Proyek ini dibuat untuk tugas kuliah Pemrograman Web II. Bebas digunakan untuk keperluan pembelajaran.
