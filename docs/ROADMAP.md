# Roadmap Pengembangan — ERP TKAB

Kerjakan berurutan. Centang `[x]` setiap item yang memenuhi Definition of Done (CLAUDE.md §12). Rincian pekerjaan yang sudah selesai ada di riwayat git.

## Selesai

- [x] Fase 0 — Fondasi: Laravel 13 + MySQL, bahasa & zona waktu Indonesia, Pest, Larastan, Pint, `composer check`
- [x] Fase 1 — Panel admin Gentelella v4: login (rate limit, tanpa "ingat saya"), layout & menu, dashboard, komponen Blade, halaman error
- [x] Fase 2 — Deploy ke Hostinger (prosedur di `docs/DEPLOY.md`)
- [x] Fase 3 — Backend Company Profile: Identitas, Hero, Layanan, Portofolio, Kategori Artikel, Artikel, FAQ, Kontak Kami; data awal lewat `CompanyProfileSeeder`

## Fase 4 — Frontend Publik

- [x] Layout publik dua tema (Full Color & Monochrome) + beranda: hero, layanan, portofolio, artikel, FAQ, kontak (formulir dengan rate limit & honeypot, peta)
- [x] Halaman error publik
- [x] Manajemen Template: satu template aktif per jenis, pratinjau, Pengaturan Default/Custom, nada dasar, ketebalan judul, bayangan
- [x] Halaman artikel: daftar, kategori, detail, kolom samping
- [x] SEO: judul & deskripsi per halaman, kanonis, Open Graph, JSON-LD, `sitemap.xml` & `robots.txt`, sakelar `SEO_INDEKS`; tombol bagikan artikel
- [ ] Halaman portofolio
- [ ] Halaman kontak
- [ ] Performa: ukuran gambar dioptimalkan, impor Basecoat selektif (`web.css` kini ±39 KB gzip), font woff2 saja (plugin font Laravel membuat blok `@font-face` woff2 & woff terpisah sehingga browser mengunduh `.woff`)
- [ ] (Opsional) Notifikasi email formulir kontak — butuh SMTP

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
