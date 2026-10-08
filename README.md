# ERP PT. Teknologi Karya Anak Bangsa

Sistem ERP berbasis web untuk PT. Teknologi Karya Anak Bangsa (TKAB), dimulai dari modul Company Profile dan dikembangkan bertahap.

- Produksi: https://karyaanakbangsa.co.id
- Stack: Laravel 13 (PHP 8.3), MySQL 8, Gentelella v4 + Vite, Pest

Panduan pengembangan ada di [CLAUDE.md](CLAUDE.md), skema database di [docs/DATABASE.md](docs/DATABASE.md), dan urutan pekerjaan di [docs/ROADMAP.md](docs/ROADMAP.md).

## Instalasi Lokal (Windows + Laragon)

Prasyarat: PHP 8.3+, Composer 2, Node.js 20+, MySQL 8 (Laragon).

1. Clone repo dan instal dependensi:
   ```bash
   git clone https://github.com/karya-anak-bangsa/laravel-erp.git
   cd laravel-erp
   composer install
   npm install
   ```
2. Buat database di MySQL Laragon (user `root`, tanpa password):
   ```sql
   CREATE DATABASE laravel_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE laravel_erp_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Siapkan `.env`, lalu isi `ADMIN_NAMA`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Migrasi database:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan di dua terminal terpisah, lalu buka http://localhost:8000:
   ```bash
   php artisan serve
   npm run dev
   ```

## Perintah Kualitas Kode

| Perintah | Fungsi |
|---|---|
| `composer lint` | Merapikan kode dengan Pint |
| `composer analyse` | Analisis statis dengan Larastan |
| `composer test` | Menjalankan test (Pest) |
| `composer check` | Pint (cek saja) + Larastan + test — wajib lulus sebelum commit |
