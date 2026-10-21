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
| `proc_open`, `exec`, `shell_exec`, `popen` dimatikan | Composer tidak bisa menjalankan script → pakai `--no-scripts` lalu `php artisan package:discover`. Apa pun yang menjalankan proses lain tidak bisa dipakai: Browsershot/wkhtmltopdf/Snappy (pilih paket PHP murni, mis. dompdf), Process facade, Concurrency driver `process`, scheduler `runInBackground`. |
| `symlink` (fungsi PHP) dimatikan | `php artisan storage:link` gagal → pakai shell `ln -s` (Server Baru langkah 5). |
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
| PHP, Blade admin, migration, route, config | Tidak |
| `resources/js`, `resources/scss`, `resources/css`, `vite.config.js`, paket npm | **Ya** |
| Blade frontend publik (`layouts/web*`, `web/**`, `components/web/**`) | **Ya** — kelas Tailwind dipindai dari berkas ini saat build |

Bila aset di-build ulang, unggah **seluruh** `public\build` dari commit yang sama dengan kode yang di-pull. Hash `admin-*.js` ikut berubah setiap ada perubahan JS publik (helper Vite bersama); itu normal.

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

Boleh (dan lebih aman) dilakukan **sebelum** `git pull`: manifest baru tetap memuat entri lama, sedangkan kode baru tanpa manifest baru menghasilkan error 500 di beranda. Paling lambat sebelum `php artisan up`.

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

## Reset Data Produksi (boleh diulang selama tahap uji)

Selama produksi masih **tahap uji** dan isinya **data dummy**, database produksi boleh dibuat ulang dari seeder kapan saja agar identik dengan lokal (CLAUDE.md §2.4). Reset juga **wajib** bila migration yang sudah jalan di produksi diedit, disusun ulang, atau dihapus, karena `php artisan migrate` biasa tidak menjalankan ulang migration yang sudah tercatat dan tidak membuang tabel yang migration-nya dihapus. Setelah pemilik menyatakan produksi memakai data real, reset tidak boleh lagi: perubahan skema lewat migration baru, perubahan data lewat admin.

Syarat: seeder menghasilkan data yang sama dengan lokal. Reset hanya mengisi ulang dari seeder, jadi perubahan lewat admin lokal yang belum dipindahkan ke seeder tidak ikut ke produksi.

Akibatnya: semua tabel dikosongkan (termasuk akun admin dan sesi login), lalu diisi ulang. Tanpa cadangan karena datanya belum bernilai.

Langkah lengkap, sekaligus dengan deploy kode:

```bash
cd ~/domains/karyaanakbangsa.co.id/laravel-erp
php artisan down
```

Bila aset berubah, unggah `public\build` sekarang (langkah 3 Deploy Rutin, dari PowerShell di laptop). Lalu di PuTTY:

```bash
git pull
# Hanya bila composer.lock berubah:
# composer install --no-dev --optimize-autoloader --no-scripts && php artisan package:discover --ansi

# 1. Buang berkas unggahan lama agar tidak tertinggal tanpa pemilik
rm -rf storage/app/public/company-profile

# 2. Buat ulang semua tabel (menggantikan "php artisan migrate --force" di Deploy Rutin)
php artisan migrate:fresh --force

# 3. Akun admin: isi ADMIN_PASSWORD='...' sementara di .env (nano .env), lalu
php artisan optimize          # config di-cache: tanpa ini .env baru tidak terbaca
php artisan db:seed --class=PenggunaSeeder --force
sed -i "s/^ADMIN_PASSWORD=.*/ADMIN_PASSWORD=/" .env

# 4. Data Company Profile (sama persis dengan lokal)
php artisan db:seed --class=CompanyProfileSeeder --force

# 5. Selesai: cache config final, lalu buka kembali situs
php artisan optimize
php artisan up
```

Cek di jendela Incognito: login admin berhasil, beranda sama dengan lokal (hero, layanan, portofolio, artikel, identitas).

---

## Server Baru dari Nol

Referensi bila server dibangun ulang.

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
   SEO_INDEKS=false         # selama tahap uji; true setelah data real masuk (lihat Akhir Tahap Uji)
   ```
5. Database, akun admin, berkas unggahan, dan data awal:
   ```bash
   chmod 600 .env
   php artisan key:generate --force
   php artisan migrate --force
   php artisan db:seed --class=PenggunaSeeder --force
   sed -i "s/^ADMIN_PASSWORD=.*/ADMIN_PASSWORD=/" .env   # password admin tidak disimpan sebagai teks biasa
   ln -s ../storage/app/public public/storage            # pengganti storage:link
   php artisan db:seed --class=CompanyProfileSeeder --force
   ```
   `CompanyProfileSeeder` wajib (tanpa itu menu Identitas Website menampilkan 404) dan aman dijalankan ulang: tabel yang sudah berisi dilewati, isian admin tidak ditimpa. Jangan memakai `php artisan db:seed` tanpa `--class`, karena `PenggunaSeeder` di dalamnya gagal setelah `ADMIN_PASSWORD` dikosongkan.
6. Unggah `public/build` (langkah 3 Deploy Rutin), lalu `php artisan optimize`.
7. Cek dari laptop: `curl -I https://www.karyaanakbangsa.co.id` harus `301` ke alamat tanpa www, dan https://karyaanakbangsa.co.id/robots.txt harus berisi `Disallow: /admin`. Bila robots.txt masih versi lama, kosongkan cache CDN lewat hPanel (pengaturan CDN situs).

## Akhir Tahap Uji (saat data real masuk)

1. Ganti `SEO_INDEKS=true` di `.env`, lalu `php artisan optimize`.
2. Daftarkan situs di Google Search Console (properti Domain, verifikasi lewat data TXT DNS di hPanel) dan kirim `https://karyaanakbangsa.co.id/sitemap.xml`. Opsional: Bing Webmaster Tools (bisa impor dari Search Console).
3. Sejak itu Reset Data Produksi tidak boleh lagi (CLAUDE.md §2.4).

---

## Aturan & Pemecahan Masalah

- **Setiap mengubah `.env`, jalankan `php artisan optimize`** — config yang di-cache tidak membaca `.env` lagi.
- **Edit `.env` lewat satu cara saja** (nano di PuTTY *atau* File Browser): tab File Browser yang masih terbuka bisa menimpa perubahan dari PuTTY, misalnya menghapus `APP_KEY` sehingga muncul error 500.
- **Error 500**: lihat judul errornya
  ```bash
  grep -h "production.ERROR" storage/logs/*.log | tail -3 | cut -c1-700
  ```
  - `No application encryption key has been specified` → `php artisan key:generate --force && php artisan optimize`
  - Error database → cek `DB_*` di `.env` (password berawalan `#` wajib dikutip)
- **Tampilan polos tanpa CSS** → `public/build` belum diunggah atau tidak lengkap (`ls public/build` harus berisi `assets`, `manifest.json`, `fonts-manifest.json`).
- **Beranda error 500 `Unable to locate file in Vite manifest: resources/css/web.css`** atau **`Font alias [...] is not defined`** → `public/build` yang terunggah berasal dari build lama. Build ulang dari commit yang sama lalu unggah seluruh `public\build`.
- **Identitas diubah langsung di database** (bukan lewat admin) tidak langsung tampil di beranda karena di-cache → `php artisan cache:forget company-profile.identitas`. Perubahan lewat admin/seeder membuang cache otomatis; `php artisan optimize` tidak menghapus cache ini.
- **Super Administrator lupa kata sandi** (fitur lupa kata sandi tidak dibuat; kata sandi Administrator yang lupa diganti Super Administrator lewat panel admin): Super Administrator lain menggantinya lewat Pengaturan Sistem › Manajemen Pengguna › Ubah. Bila tidak ada Super Administrator lain, di PuTTY:
  ```bash
  php artisan tinker
  App\Models\Pengguna::where('email', 'email@akun')->firstOrFail()->update(['password' => 'kata-sandi-sementara']);
  exit
  ```
  Kata sandi otomatis di-hash dan sesi akun itu di perangkat lain berakhir, tetapi teksnya tersimpan di riwayat tinker, jadi pakai kata sandi sementara lalu segera ganti lewat Profil Saya.
- **Tema beranda tertukar antarpengunjung** → bila kelak LiteSpeed Cache diaktifkan di hPanel, kecualikan halaman publik dari cache (HTML berbeda per cookie `tema`).
- **HTTPS & www**: pengalihan HTTP → HTTPS ditangani Hostinger; `www.` → tanpa www (301) ditangani aturan `RewriteCond %{HTTP_HOST} ^www\.` di `public/.htaccess`, ikut ter-deploy lewat `git pull`.
- **robots.txt & sitemap.xml** dibuat Laravel (`Web\SeoController`). Jangan membuat `public/robots.txt`: berkas statis didahulukan server sehingga aturan dari aplikasi tidak terpakai.
