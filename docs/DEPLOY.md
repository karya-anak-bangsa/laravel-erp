# Deploy ke Hostinger — ERP TKAB

Deploy dilakukan **manual oleh pemilik proyek** lewat SSH (PuTTY). Alamat, port, dan username SSH ada di hPanel → Advanced → SSH Access (port Hostinger: `65002`). Jangan menulis kredensial apa pun di file ini — repo bersifat publik.

---

## Struktur di Server

```
~/domains/karyaanakbangsa.co.id/
├── laravel-erp/                  ← clone repo (di luar web root)
├── public_html -> laravel-erp/public   ← symlink; domain mengarah ke sini
└── DO_NOT_UPLOAD_HERE            ← penanda bawaan Hostinger, biarkan
```

- `public_html` **bukan salinan**, melainkan `laravel-erp/public` itu sendiri. Jangan menghapus isinya lewat File Browser.
- `.env`, `vendor/`, `storage/` berada di luar web root sehingga tidak bisa diakses dari browser.

## Batasan Server (shared hosting)

| Batasan | Dampak & solusi |
|---|---|
| `proc_open`, `exec`, `shell_exec`, `popen` dimatikan | Composer tidak bisa menjalankan script → pakai `--no-scripts` lalu `php artisan package:discover`. Paket yang menjalankan program eksternal (Browsershot, wkhtmltopdf, Snappy) tidak bisa dipakai — pilih paket PHP murni (mis. dompdf). |
| `symlink` (fungsi PHP) dimatikan | `php artisan storage:link` gagal → pakai shell `ln -s` (lihat bagian Satu Kali per Fase di bawah). |
| Tidak ada Node/npm | Aset Vite di-build di laptop lalu diunggah (`public/build` ada di `.gitignore`). |
| `mail()` dimatikan | Email (bila nanti dibutuhkan) wajib lewat SMTP. |

---

## Deploy Rutin

### 1. Di laptop

```bash
composer check          # wajib lulus
npm run build           # hanya bila aset berubah (lihat tabel di bawah)
git push
```

| Perubahan | Perlu build & unggah aset? |
|---|---|
| PHP, Blade, migration, route, config | Tidak |
| `resources/js`, `resources/scss`, `vite.config.js`, paket npm | **Ya** |

### 2. Di server (PuTTY)

```bash
cd ~/domains/karyaanakbangsa.co.id/laravel-erp
php artisan down
git pull
composer install --no-dev --optimize-autoloader --no-scripts
php artisan package:discover --ansi
php artisan migrate --force
```

### 3. Unggah aset (bila perlu) — dari PowerShell di laptop

```powershell
cd C:\laragon\www\project\laravel-erp
pscp -P 65002 -r public\build <user>@<ip-server>:domains/karyaanakbangsa.co.id/laravel-erp/public/
```

File lama ditimpa; berkas ber-hash lama yang tertinggal tidak berbahaya. Alternatif: unggah folder `public\build` lewat File Browser ke `laravel-erp/public/`.

### 4. Selesaikan (PuTTY)

```bash
php artisan optimize
php artisan up
```

Lalu buka https://karyaanakbangsa.co.id di jendela Incognito dan cek fitur yang baru di-deploy.

---

## Deploy Pertama (sudah dilakukan 2026-10-07 — referensi bila server dibangun ulang)

1. hPanel: aktifkan SSH, set PHP 8.3, buat database MySQL (`u491519120_erp`), pastikan SSL aktif.
2. Clone & symlink:
   ```bash
   cd ~/domains/karyaanakbangsa.co.id
   git clone https://github.com/karya-anak-bangsa/laravel-erp.git
   mv public_html public_html_lama
   ln -s laravel-erp/public public_html
   ```
3. `composer install --no-dev --optimize-autoloader --no-scripts` lalu `php artisan package:discover --ansi`.
4. Buat `.env` produksi dari `.env.example` dengan perubahan:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://karyaanakbangsa.co.id
   LOG_STACK=daily
   LOG_LEVEL=error
   DB_HOST=localhost
   DB_DATABASE=...
   DB_USERNAME=...
   DB_PASSWORD='...'        # kutip tunggal: '#' di awal dianggap komentar, '$' di kutip ganda diproses
   ADMIN_NAMA="..."
   ADMIN_EMAIL=...
   ADMIN_PASSWORD='...'
   SESSION_SECURE_COOKIE=true
   ```
5. ```bash
   chmod 600 .env
   php artisan key:generate --force
   php artisan migrate --force
   php artisan db:seed --class=PenggunaSeeder --force
   sed -i "s/^ADMIN_PASSWORD=.*/ADMIN_PASSWORD=/" .env   # password admin tidak disimpan sebagai teks biasa
   ```
6. Unggah `public/build` (langkah 3 deploy rutin), lalu `php artisan optimize`.

## Satu Kali per Fase

- **Company Profile (sebelum upload gambar pertama)** — buat symlink storage:
  ```bash
  cd ~/domains/karyaanakbangsa.co.id/laravel-erp
  ln -s ../storage/app/public public/storage
  ```
  File unggahan berada di `storage/app/public` (tidak masuk Git), sehingga aman dari `git pull`.
- **Company Profile — Identitas** (setelah migration `tb_identitas`, setelah symlink di atas) — buat baris identitas tunggal beserta logo & favicon awal:
  ```bash
  php artisan db:seed --class=IdentitasSeeder --force
  ```
  Aman dijalankan ulang (tidak menimpa isian admin). Tanpa langkah ini menu Identitas menampilkan 404.
- **Company Profile — Hero** (setelah migration `tb_hero`) — buat satu hero awal yang aktif dengan gambar `public/img/hero.webp`:
  ```bash
  php artisan db:seed --class=HeroSeeder --force
  ```
  Aman dijalankan ulang. Opsional: tanpa langkah ini daftar hero kosong dan admin bisa menambah sendiri.
  Data contoh (4 hero nonaktif bergambar ilustrasi) hanya otomatis di lokal; di server opsional, jalankan **setelah** `HeroSeeder`:
  ```bash
  php artisan db:seed --class=HeroDummySeeder --force
  ```
- **Company Profile — Layanan** (setelah migration `tb_layanan`) — opsional, mengisi 5 layanan perusahaan beserta gambar ilustrasi:
  ```bash
  php artisan db:seed --class=LayananSeeder --force
  ```
  Dilewati bila tabel layanan sudah berisi, jadi aman dijalankan ulang.

---

## Aturan & Pemecahan Masalah

- **Setiap mengubah `.env`, jalankan `php artisan optimize`** — config yang di-cache tidak membaca `.env` lagi.
- **Edit `.env` lewat satu cara saja** (nano di PuTTY *atau* File Browser). Tab File Browser yang masih terbuka bisa menimpa perubahan dari PuTTY — pada deploy pertama ini menghapus `APP_KEY` dan menyebabkan error 500.
- **Error 500**: lihat judul errornya
  ```bash
  grep -h "production.ERROR" storage/logs/*.log | tail -3 | cut -c1-700
  ```
  - `No application encryption key has been specified` → `php artisan key:generate --force && php artisan optimize`
  - Error database → cek `DB_*` di `.env` (password berawalan `#` wajib dikutip)
- **Tampilan polos tanpa CSS** → `public/build` belum diunggah atau tidak lengkap (`ls public/build` harus berisi `assets`, `manifest.json`, `fonts-manifest.json`).
- **HTTPS**: pengalihan HTTP → HTTPS sudah ditangani Hostinger; `public/.htaccess` tidak perlu diubah.
