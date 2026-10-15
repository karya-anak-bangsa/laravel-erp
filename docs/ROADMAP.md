# Roadmap Pengembangan — ERP TKAB

Kerjakan berurutan. Centang `[x]` setiap item yang memenuhi Definition of Done (CLAUDE.md §12).

**Alasan urutan**: panel admin wajib terlindungi login sebelum online, jadi dashboard & autentikasi dikerjakan bersama. Deploy dilakukan sedini mungkin agar masalah server ketahuan saat aplikasi masih kecil. Backend Company Profile dikerjakan sebelum frontend publik karena frontend menampilkan datanya.

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
- [x] `layouts/admin.blade.php`: sidebar dari `config/menu.php` (menu aktif otomatis), topbar (nama pengguna, logout), breadcrumb, footer (toggle dark mode dihapus 2026-10-08 atas keputusan pemilik)
- [x] Dashboard placeholder (kartu ringkasan kosong, diisi seiring modul ditambahkan)
- [x] Feature test autentikasi & akses dashboard
### 1b. Komponen & halaman error
- [x] Komponen Blade admin dasar: page-header, card, form-input/textarea/select/file (dengan error), delete-button + modal konfirmasi, empty-state, pagination, toast flash message
- [x] Halaman error 403, 404, 419, 500, 503 bergaya Gentelella

## Fase 2 — Deploy ke Hostinger
Dilakukan **sendiri oleh pemilik proyek** setelah Fase 1a. Setelah itu, setiap fitur yang selesai langsung di-deploy agar progres bisa diakses online.
- [x] Deploy pertama & smoke test produksi: login, dashboard, halaman 404, HTTPS paksa (langkah & catatan server di `docs/DEPLOY.md`)

## Fase 3 — Company Profile (Backend)
- [x] Identitas (singleton: edit + upload logo & favicon) + `IdentitasSeeder`
- [x] Kategori Artikel (data master abjad, jumlah artikel, tolak hapus bila masih dipakai) + `KategoriArtikelSeeder` (4 kategori)
- [x] Artikel (slug otomatis, sanitasi HTML, editor rich text dengan sub-judul H2/H3, status draf/terbit, filter kategori & status) + `ArtikelDummySeeder`
- [x] Layanan (urutan) + `LayananSeeder` (5 layanan perusahaan)
- [x] Hero (repeater keyword & CTA, status aktif — boleh banyak, hanya satu aktif)
- [x] Portofolio (slug otomatis, kategori teks bebas dengan saran & filter) + `PortofolioDummySeeder`
- [x] FAQ (urutan, jawaban WYSIWYG) + `FaqSeeder` (5 FAQ awal)
- [x] Kontak Kami (kotak masuk: filter, tandai dibaca/belum — otomatis saat modal detail dibuka, hapus) + badge jumlah belum dibaca di sidebar (kotak masuk awal kosong, diisi form kontak Fase 4)
- [x] Data awal lokal & produksi seragam lewat `CompanyProfileSeeder`; migration Company Profile disusun ulang sebelum deploy pertama modul ini

## Fase 4 — Frontend Publik
Dua tema dalam satu markup — **Full Color** (bawaan) dan **Monochrome** — diterapkan bertahap (keputusan pemilik 2026-10-09).
- [x] Putuskan template: Tailwind v4 + Basecoat UI, dua tema Full Color & Monochrome; dicatat di CLAUDE.md §4
- [x] Langkah 1 — Prototipe gabungan `tema-ganda.html` (di luar repo) disetujui pemilik (2026-10-09; Full Color memakai sudut sedang)
- [x] Langkah 2a — Layout publik terpisah dari admin (`layouts/web`, entri Vite sendiri) + beranda (hero, layanan, portofolio, artikel terbaru, FAQ, kontak) dengan dua template bawaan dan tombol ganti tema di navbar (bawaan Full Color, pilihan pengunjung disimpan di cookie); data identitas di-cache; peta Leaflet + OSM (titik Jalan Pipit III dari Nominatim). Kartu artikel belum bertautan sampai halaman artikel dibuat
- [x] Langkah 2b — Halaman error publik (404/419/500/503 + 429/4xx/5xx bergaya tema frontend dengan navbar & footer, tanpa tautan ke `/admin`; tampil ringkas bila database tidak terbaca); halaman admin tetap memakai layout error admin
- [x] Langkah 3a — Manajemen Template (Pengaturan Sistem): `tb_template`, tambah/ubah/hapus, aktivasi satu template per jenis (template aktif tidak bisa dihapus/dinonaktifkan/diganti jenisnya), pratinjau beranda di tab baru termasuk isian yang belum disimpan; tema frontend diambil dari template aktif (cadangan `config/tema.php`); semua font pilihan dibangun sampai bobot 800; ketebalan judul
- [x] Langkah 3b — Pilihan bayangan template (tanpa/halus/tegas) untuk tombol, isian, kartu, dan bingkai gambar hero di kedua tema (Full Color juga navbar, kotak ajakan, ikon kontak); elemen melayang tetap berbayangan; Halus identik dengan tampilan sebelumnya
- [x] Langkah 3c — Perbaikan dari review pemilik (2026-10-14): nada dasar Monochrome yang bedanya terlihat (Netral, Biru, Hijau, Ungu, Cokelat); pilihan Pengaturan Default/Custom di form template (Default mengunci isian dan memakai nilai bawaan); nilai bawaan `config/tema.php` = data template lokal
- [x] Halaman artikel (2026-10-15): daftar `/artikel` (9 per halaman, paginasi) + detail `/artikel/{slug}` (kolom baca, artikel lainnya, band ajakan); kartu artikel beranda bertautan + tombol "Lihat semua artikel"; menu Artikel di navbar & footer ke halaman daftar; slug artikel dikunci setelah terbit
- [ ] Halaman: portofolio, kontak
- [x] Form kontak → `tb_kontak_kami` (rate limit 3/menit per IP, honeypot) — dikerjakan bersama langkah 2a; notifikasi email (opsional, butuh SMTP) belum dibuat
- [ ] SEO: meta title/description dari identitas (sudah di langkah 2a; halaman artikel memakai judul & ringkasan artikel), Open Graph + tombol bagikan artikel (WhatsApp, salin tautan), `sitemap.xml`, `robots.txt`
- [ ] Performa: lazy-load gambar (sudah di langkah 2a), ukuran gambar dioptimalkan, impor Basecoat selektif (`web.css` kini ±39 KB gzip), font woff2 saja (plugin font Laravel 3.2 membuat blok `@font-face` woff2 & woff terpisah sehingga browser mengunduh `.woff`)

## Fase 5 — Penguatan Kualitas
- [ ] Manajemen pengguna & role/permission (nama tabel mengikuti konvensi `tb_`)
- [ ] Audit log aktivitas
- [ ] Backup database terjadwal
- [ ] Header keamanan, review OWASP dasar
- [ ] Naikkan level Larastan, tinjau cakupan test
- [ ] (Opsional) GitHub Actions untuk menjalankan test otomatis setiap push

## Fase 6+ — Ekspansi ERP (gambaran)
- [ ] Peserta (master bersama)
- [ ] Pelatihan, Sertifikasi, Bootcamp (+ pendaftaran)
- [ ] Klien, Proyek, Invoice
