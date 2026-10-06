# Roadmap Pengembangan — ERP TKAB

Kerjakan berurutan. Centang `[x]` setiap item yang memenuhi Definition of Done (CLAUDE.md §12).

**Alasan urutan**: panel admin wajib terlindungi login sebelum online, jadi dashboard & autentikasi dikerjakan bersama. Deploy dilakukan sedini mungkin agar masalah server ketahuan saat aplikasi masih kecil. Kas Perusahaan didahulukan dari Company Profile karena langsung berguna (pengeluaran perizinan & domain sudah terjadi dan perlu dicatat), sedangkan data Company Profile baru bermanfaat setelah frontend publik ada.

---

## Fase 0 — Fondasi Proyek
- [x] Instal Laravel 13 di repo `laravel-erp` (folder `C:\laragon\www\project\laravel-erp`), hubungkan remote GitHub
- [x] `.env`: MySQL `laravel_erp`, `APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, `APP_TIMEZONE`/config `Asia/Jakarta`, `APP_URL=http://localhost:8000`
- [x] `.env.example` lengkap (tanpa nilai rahasia), termasuk `ADMIN_NAMA`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`
- [x] Pest, Larastan (level 5, dinaikkan bertahap), Pint; script composer `lint`, `analyse`, `test`, `check`
- [x] `phpunit.xml` memakai database `laravel_erp_testing` (MySQL)
- [x] `lang/id/validation.php` dan pesan dasar berbahasa Indonesia
- [x] Kerangka folder sesuai CLAUDE.md §6 (`routes/admin.php`, `config/menu.php`, `app/Support`, `app/Services/Shared`)
- [x] `.editorconfig`, README singkat (cara instal lokal)

## Fase 1 — Tampilan Dashboard Backend & Autentikasi
### 1a. Login & dashboard terlindungi (lalu deploy pertama)
- [x] Instal Gentelella v4 via npm (ganti Tailwind bawaan); `resources/scss/admin.scss` + `resources/js/admin.js` di Vite
- [x] Migration `tb_pengguna` (sesuaikan migration bawaan), model `Pengguna`, `PenggunaSeeder` dari env
- [x] `layouts/auth.blade.php` + halaman login; login (rate limit, tanpa "ingat saya"), logout, redirect tamu ke login, `/` dialihkan ke `/admin`
- [x] `layouts/admin.blade.php`: sidebar dari `config/menu.php` (menu aktif otomatis), topbar (nama pengguna, toggle dark mode, logout), breadcrumb, footer
- [x] Dashboard placeholder (kartu ringkasan kosong, siap diisi Fase 3c)
- [x] Feature test autentikasi & akses dashboard
### 1b. Komponen & halaman error
- [x] Komponen Blade admin dasar: page-header, card, form-input/textarea/select/file (dengan error), delete-button + modal konfirmasi, empty-state, pagination, toast flash message
- [x] Halaman error 403, 404, 419, 500, 503 bergaya Gentelella

## Fase 2 — Deploy ke Hostinger
Dilakukan **sendiri oleh pemilik proyek** setelah Fase 1a. Setelah itu, setiap fitur yang selesai langsung di-deploy agar progres bisa diakses online.
- [x] Deploy pertama & smoke test produksi: login, dashboard, halaman 404, HTTPS paksa (langkah & catatan server di `docs/DEPLOY.md`)

## Fase 3 — Kas Perusahaan
Tujuan utama: pemasukan dan pengeluaran tercatat. 3a–3c adalah inti; 3d dan 3e **opsional**, dikerjakan hanya bila dibutuhkan.

### 3a. Master
- [ ] Enum `JenisAkunKas` (selesai), `JenisTransaksi`
- [x] CRUD Akun Kas
- [ ] CRUD Kategori Transaksi + `KategoriTransaksiSeeder`
### 3b. Transaksi
- [ ] `TransaksiKasService` (penomoran, validasi kecocokan jenis, transaksi DB) + unit test
- [ ] CRUD Transaksi Kas: filter periode/akun/kategori/jenis, upload & lihat bukti (disk privat), `created_by/updated_by`
### 3c. Laporan & Dashboard
- [ ] Saldo per akun (unit test perhitungan)
- [ ] Laporan arus kas per periode: total pemasukan, pengeluaran, selisih; rincian per kategori
- [ ] Widget dashboard: saldo total, pemasukan & pengeluaran bulan ini, grafik 12 bulan (ECharts), transaksi terbaru
### 3d. Transfer antar akun (opsional)
- [ ] Migration & CRUD `tb_transfer_kas`, terintegrasi ke perhitungan saldo
### 3e. Ekspor (opsional)
- [ ] Ekspor laporan ke Excel dan PDF (pilih paket setelah cek kompatibilitas Laravel 13)

## Fase 4 — Company Profile (Backend)
- [ ] Identitas (singleton: edit + upload logo & favicon) + `IdentitasSeeder`
- [ ] Kategori Artikel
- [ ] Artikel (slug otomatis, sanitasi HTML, editor rich text, status draf/terbit)
- [ ] Layanan (urutan)
- [ ] Hero (repeater keyword & CTA, status aktif)
- [ ] Portofolio (slug, kategori)
- [ ] FAQ (urutan)
- [ ] Kontak Kami (kotak masuk: filter, tandai dibaca/belum, hapus) + badge jumlah belum dibaca di sidebar

## Fase 5 — Frontend Publik
- [ ] Putuskan template (BootstrapMade vs Tailwind custom) dan catat di CLAUDE.md §4
- [ ] Layout publik terpisah dari admin (`layouts/web`), data identitas di-cache
- [ ] Halaman: beranda (hero, layanan, portofolio, FAQ), portofolio, artikel + detail (slug), kontak
- [ ] Form kontak → `tb_kontak_kami` (rate limit, honeypot, notifikasi email opsional)
- [ ] SEO: meta title/description dari identitas, Open Graph, `sitemap.xml`, `robots.txt`
- [ ] Performa: lazy-load gambar, ukuran gambar dioptimalkan

## Fase 6 — Penguatan Kualitas
- [ ] Manajemen pengguna & role/permission (nama tabel mengikuti konvensi `tb_`)
- [ ] Audit log aktivitas
- [ ] Backup database terjadwal
- [ ] Header keamanan, review OWASP dasar
- [ ] Naikkan level Larastan, tinjau cakupan test
- [ ] (Opsional) GitHub Actions untuk menjalankan test otomatis setiap push

## Fase 7+ — Ekspansi ERP (gambaran)
- [ ] Peserta (master bersama)
- [ ] Pelatihan, Sertifikasi, Bootcamp (+ pendaftaran, terhubung ke Kas via `referensi`)
- [ ] Klien, Proyek, Invoice
