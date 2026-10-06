# Deployment — Hostinger (karyaanakbangsa.co.id)

**Paket**: Hostinger Web Hosting **Unlimited** (dahulu *Business*) — SSH, Composer, dan Node.js tersedia.

> Nilai bertanda **[cek]** harus dipastikan di hPanel/SSH, lalu perbarui file ini dengan nilai sebenarnya.

## Alur Deploy (manual, dilakukan pemilik proyek)

1. Di laptop: `composer check` lulus → commit → `git push origin main`.
2. Buka PuTTY → SSH ke server Hostinger.
3. `cd ~/domains/karyaanakbangsa.co.id/laravel-erp` → `git pull origin main`.
4. Jalankan perintah lanjutan sesuai perubahan (lihat tabel di bawah) — atau cukup `bash deploy.sh` untuk menjalankan semuanya.

Tujuannya agar setiap fitur yang selesai **langsung dapat diakses** di https://karyaanakbangsa.co.id. Karena itu `main` harus selalu dalam kondisi siap produksi.

Tidak ada deploy otomatis. Fitur hPanel "Node.js Web App" dan auto-deploy Git hPanel **tidak dipakai** (auto-deploy hPanel menyalin repo ke `public_html`, tidak cocok untuk Laravel).

## Perintah Setelah git pull

Claude Code wajib menyertakan daftar ini (hanya baris yang relevan) di akhir setiap fitur.

| Jika yang berubah… | Jalankan |
|---|---|
| Selalu (sebelum mulai) | `php artisan down --retry=60` |
| `composer.json` / `composer.lock` | `composer install --no-dev --optimize-autoloader --no-interaction` |
| `package.json` / `package-lock.json` | `npm ci --no-audit --no-fund` |
| `resources/` (Blade tidak termasuk), `vite.config.js`, atau paket npm | `npm run build` |
| File baru di `database/migrations/` | `php artisan migrate --force` |
| Seeder baru yang perlu dijalankan sekali | `php artisan db:seed --class=NamaSeeder --force` |
| Variabel `.env` baru | edit `.env` di server (`nano .env`) sebelum langkah berikutnya |
| Selalu (penutup) | `php artisan optimize:clear && php artisan optimize && php artisan up` |

## Skrip `deploy.sh` (di root repo)

Menjalankan semua langkah sekaligus — aman dipakai setiap kali walau tidak semua langkah diperlukan.

```bash
#!/usr/bin/env bash
set -euo pipefail
cd ~/domains/karyaanakbangsa.co.id/laravel-erp

php artisan down --retry=60
trap 'php artisan up' EXIT          # situs selalu kembali online walau ada langkah gagal

git pull --ff-only origin main
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit --no-fund
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```
Pemakaian di PuTTY: `bash ~/domains/karyaanakbangsa.co.id/laravel-erp/deploy.sh`

## Persiapan Satu Kali

### hPanel
1. SSH Access aktif. Catat host, port (umumnya `65002`) **[cek]**, username.
2. Versi PHP domain **8.3+**, ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `intl`, `gd`/`imagick`.
3. Database MySQL + user (nama berprefiks otomatis, mis. `u123456789_tkab`).
4. SSL aktif.

### Verifikasi binary di SSH
```bash
php -v        # 8.3+ (jika beda dengan versi web, pakai binary versi yang benar) [cek]
composer -V   # Composer 2
node -v       # 20+ (syarat Gentelella v4)
npm -v        # 10+
```
Jika `node`/`npm` tidak ada di PATH, tambahkan path binary Node dari Hostinger (mis. di bawah `/opt/alt/`) ke `~/.bashrc` **[cek]**.

Hasil pengecekan:
- PHP CLI: `________`
- Node/npm: `________`

### Struktur direktori
```
~/domains/karyaanakbangsa.co.id/
├── laravel-erp/           ← repo hasil clone
│   ├── public/
│   ├── storage/
│   └── .env               ← dibuat manual, chmod 600
└── public_html  →  laravel-erp/public   (symlink)
```
`public_html` dijadikan symlink karena Hostinger tidak menyediakan opsi mengganti document root. Dengan cara ini `.env`, `vendor/`, `node_modules/`, dan `storage/` tidak dapat diakses publik.

### Deploy pertama
```bash
cd ~/domains/karyaanakbangsa.co.id/laravel-erp     # setelah git clone
cp .env.example .env && nano .env
# APP_ENV=production, APP_DEBUG=false, APP_URL=https://karyaanakbangsa.co.id,
# DB_*, ADMIN_*, SESSION_SECURE_COOKIE=true, LOG_LEVEL=warning
chmod 600 .env

composer install --no-dev --optimize-autoloader
npm ci --no-audit --no-fund && npm run build
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=PenggunaSeeder --force
php artisan storage:link
php artisan optimize

cd ~/domains/karyaanakbangsa.co.id
mv public_html public_html_backup
ln -s laravel-erp/public public_html
```

### Cron (hPanel → Cron Jobs), tiap menit
`cd ~/domains/karyaanakbangsa.co.id/laravel-erp && php artisan schedule:run >> /dev/null 2>&1` **[cek path PHP]**

Queue: `QUEUE_CONNECTION=sync` dulu; bila nanti ada pekerjaan berat, pindah ke `database` + `queue:work --stop-when-empty` lewat scheduler.

## Checklist Setelah Deploy

- [ ] `https://karyaanakbangsa.co.id/admin` mengarah ke login, HTTP dialihkan ke HTTPS
- [ ] Fitur yang baru di-deploy berfungsi
- [ ] CSS/JS admin termuat
- [ ] Halaman tak dikenal menampilkan 404 kustom (bukan stack trace)
- [ ] `https://karyaanakbangsa.co.id/.env` tidak dapat diakses
- [ ] `storage/logs/laravel.log` tidak berisi error baru

## Pemulihan

- Kode bermasalah: `git revert <commit>` di laptop → push → `git pull` + `deploy.sh` di server.
- Sebelum menjalankan migration yang mengubah/menghapus kolom, backup dulu: `mysqldump -u USER -p NAMA_DB > ~/backup_$(date +%F).sql`. Jangan menjalankan `migrate:fresh`/`migrate:rollback` di produksi.
