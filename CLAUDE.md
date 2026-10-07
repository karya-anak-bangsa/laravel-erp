# CLAUDE.md — ERP PT. Teknologi Karya Anak Bangsa (TKAB)

File ini adalah panduan utama untuk Claude Code saat bekerja di repositori ini.
Baca seluruhnya di awal sesi. Detail lanjutan ada di folder `docs/` dan **dibaca saat relevan**:

| File | Baca ketika |
|---|---|
| `docs/DATABASE.md` | Membuat/mengubah migration, model, factory, seeder, relasi, validasi `exists`/`unique` |
| `docs/ROADMAP.md` | Memulai sesi, memilih tugas berikutnya, atau menandai progres |
| `docs/DEPLOY.md` | Menyusun catatan deploy, menambah paket/perintah yang harus jalan di server Hostinger |

---

## 1. Tentang Proyek

- **Perusahaan**: PT. Teknologi Karya Anak Bangsa — jasa pembuatan website, mobile apps, pelatihan IT, sertifikasi IT, dan bootcamp mahasiswa.
- **Produk**: Sistem ERP berbasis web yang dikembangkan **bertahap dan konsisten**.
- **Modul saat ini**:
  1. **Company Profile** — identitas, hero, layanan, portofolio, artikel + kategori, FAQ, kontak kami.
  2. **Kas Perusahaan** — pencatatan pemasukan & pengeluaran per akun kas.
- **Modul masa depan** (desain hari ini harus siap menampungnya): riwayat pelatihan, sertifikasi, bootcamp, klien/proyek, invoice.
- **Fokus saat ini**: backend (panel admin). Frontend publik dikerjakan belakangan.
- **Standar kualitas**: ISO/IEC 25010 (lihat §11).
- **Domain produksi**: https://karyaanakbangsa.co.id (Hostinger)
- **Repositori**: https://github.com/karya-anak-bangsa/laravel-erp

## 2. Aturan Kerja untuk Claude

1. **Bahasa**: berkomunikasi dalam Bahasa Indonesia. Komentar kode dalam Bahasa Indonesia, singkat, hanya untuk menjelaskan *mengapa*, bukan *apa*.
2. **Rencana dulu**: untuk fitur baru atau perubahan yang menyentuh lebih dari 2 file, tuliskan rencana singkat (daftar file yang dibuat/diubah + alasannya) dan tunggu persetujuan sebelum menulis kode.
3. **Patuhi skema**: jangan menambah/mengubah/menghapus kolom di luar `docs/DATABASE.md` tanpa persetujuan. Jika ada kebutuhan, usulkan dulu lalu perbarui `docs/DATABASE.md`.
4. **Migration yang sudah jalan di produksi tidak boleh diedit** — buat migration baru untuk perubahan.
5. **Paket baru** (composer/npm) harus diminta izin dulu, sebutkan alasannya dan pastikan kompatibel dengan Laravel 13 / PHP 8.3.
6. **Git**: semua commit langsung ke `main` (developer tunggal). Deploy ke Hostinger dilakukan **sendiri oleh pemilik proyek** (Claude tidak melakukan deploy), sehingga `main` harus selalu dalam kondisi siap dipakai di produksi:
   - Jangan commit atau push kecuali diminta.
   - Sebelum commit, wajib lulus `composer check` (lint + analisis statis + test).
   - Format pesan: Conventional Commits berbahasa Indonesia, scope = modul.
     Contoh: `feat(kas): tambah CRUD transaksi kas`, `fix(company-profile): perbaiki validasi slug artikel`, `chore(deploy): perbarui workflow`.
   - Satu commit = satu perubahan logis.
7. **Rahasia**: jangan pernah menulis kredensial ke kode, docs, atau commit. `.env` tidak boleh di-commit.
8. **Selesai berarti memenuhi Definition of Done** (§12), lalu centang item terkait di `docs/ROADMAP.md`.
9. **Kecil dan sering online**: pemilik proyek ingin setiap kemajuan langsung bisa diakses di https://karyaanakbangsa.co.id. Pecah pekerjaan menjadi fitur kecil yang masing-masing utuh dan bisa di-deploy. Jangan meninggalkan `main` dalam kondisi setengah jadi (menu yang mengarah ke halaman rusak, migration tanpa fitur, dsb.).
10. **Catatan deploy**: di akhir setiap fitur, sebutkan singkat apa yang berdampak ke server: migration baru, seeder yang perlu dijalankan sekali, variabel `.env` baru, paket composer/npm baru, dan apakah aset perlu di-build ulang.
11. Jika instruksi pengguna bertentangan dengan file ini, ikuti pengguna lalu tawarkan untuk memperbarui file ini.

## 3. Lingkungan Pengembangan

- **OS**: Windows, dengan **Laragon 8.4.0** — Apache 2.4.62, PHP 8.3.28, MySQL 8.0.40, Node.js 24.12, Git 2.47.1, Composer 2.10.1.
- **Lokasi proyek**: `C:\laragon\www\project\laravel-erp`, dijalankan dengan `php artisan serve` → `http://localhost:8000` (tanpa virtual host Laragon; Laragon hanya dipakai untuk MySQL).
- **Editor**: VS Code + Claude Code. Tulis perintah terminal yang berjalan di Git Bash maupun PowerShell (hindari sintaks khusus Linux seperti `sudo`, `&&` pada PowerShell lama).
- **Perangkat**: laptop 16 GB RAM, layar 1920×1200 — target utama tampilan panel admin, tetapi wajib tetap responsif di tablet & ponsel.
- **Database lokal**: MySQL `laravel_erp` (user `root`, tanpa password — default Laragon). Database test: `laravel_erp_testing`.
- Catatan: skeleton Laravel 13 default memakai SQLite — proyek ini **wajib MySQL** (`DB_CONNECTION=mysql`).

## 4. Tech Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13 (PHP ≥ 8.3) |
| Database | MySQL 8, charset `utf8mb4_unicode_ci` |
| Template admin | **Gentelella v4** (npm `gentelella`) — vanilla JS + SCSS + Vite. **Tanpa Bootstrap, tanpa jQuery.** |
| Build aset | Vite (laravel-vite-plugin) |
| Grafik | ECharts (bawaan Gentelella v4) |
| Toast & konfirmasi | Simple Notify (toast) dan SweetAlert2 (dialog konfirmasi), npm |
| Testing | Pest (di atas PHPUnit) |
| Kualitas kode | Laravel Pint (preset `laravel`), Larastan |
| Frontend publik | Belum diputuskan: BootstrapMade (lisensi seluruh template sudah dibeli) vs Tailwind custom — **jangan dikerjakan sebelum Fase 5**. Tailwind bawaan skeleton sudah dihapus; dipasang lagi di Fase 5 bila dipilih, terpisah dari aset admin |

### Integrasi Gentelella v4 dengan Laravel
- Pasang via npm, lalu impor SCSS & modul JS di `resources/scss/admin.scss` dan `resources/js/admin.js`; dikompilasi oleh Vite Laravel.
- Gentelella v4 menyuntikkan shell (sidebar/topbar) lewat JavaScript. Di proyek ini **sidebar, topbar, dan breadcrumb dirender server-side dengan Blade** (agar menu aktif, nama pengguna, dan otorisasi dikendalikan Laravel). Salin markup dari halaman referensi `production/*.html` dan `src/v4/shell-render.js`. `mountShell()` (di `resources/js/admin.js`) boleh dipanggil karena hanya memasang perilaku bila markup sudah ada; jangan pakai kelas `.tb-avatar`, `.tb-notifications`, `.tb-messages`, `.sidebar-user .more-btn`, `.theme-toggle`, dan kotak pencarian topbar karena Gentelella mengikatnya ke menu/data demo. Menu dropdown dibuat dengan tombol ber-atribut `data-menu="<id>"` + `<template id="<id>">` berisi markup Blade (`.menu-item`, `.menu-separator`); `admin.js` membukanya lewat `openPanel()`. Contoh: menu pengguna di `.sidebar-footer` (toggle tema + logout); topbar hanya berisi tombol sidebar + breadcrumb. Layout admin terdiri dari `layouts/admin.blade.php` + `layouts/partials/{head,sidebar,topbar,footer}`; breadcrumb dikirim lewat `@extends('layouts.admin', ['breadcrumb' => ['Label' => url|null]])`.
- Komponen JS Gentelella (chart, menu `openPanel`) boleh dipakai langsung. **Toast memakai Simple Notify dan dialog konfirmasi memakai SweetAlert2** (pilihan pemilik) — jangan memakai `showToast`/`showModal` Gentelella. Keduanya dipasang di `resources/js/admin.js` (sumber: flash session & atribut `data-confirm`), CSS-nya di `admin.scss` dengan token Gentelella agar ikut mode gelap. Keamanan: `text` Simple Notify dirender sebagai HTML → selalu di-escape; di SweetAlert2 pakai `titleText`/`text`, bukan `title`/`html`, untuk isi yang memuat input pengguna.
- Jangan memakai kelas Bootstrap — Gentelella v4 tidak memuatnya. Gunakan kelas/komponen Gentelella; cek halaman *Component playground* untuk markup yang benar.
- Tabel daftar data memakai **paginasi & pencarian server-side Laravel**, bukan DataTables client-side (performa saat data membesar).

## 5. Perintah Umum

```bash
composer install && npm install        # instal dependensi
php artisan serve                       # server PHP di http://localhost:8000
npm run dev                             # Vite dev server (jalankan bersamaan dengan artisan serve)
npm run build                           # build aset produksi
php artisan migrate --seed              # migrasi + seeder
php artisan migrate:fresh --seed        # reset database lokal (JANGAN di produksi)
php artisan test                        # jalankan test
composer lint                           # vendor/bin/pint
composer analyse                        # vendor/bin/phpstan analyse
composer check                          # pint --test + phpstan + test (wajib sebelum commit)
```
Script `lint`, `analyse`, `check` didefinisikan di `composer.json` pada Fase 0.

## 6. Arsitektur

### Prinsip
- **Modular per domain** di dalam struktur standar Laravel (tanpa paket modul eksternal). Setiap modul punya namespace sendiri di setiap lapisan. Menambah modul baru = menambah folder namespace baru, tanpa mengubah modul lain.
- **Controller tipis**: menerima request → memanggil Model/Service → mengembalikan view/redirect.
- **Validasi selalu di Form Request**, tidak pernah di controller.
- **Service** dipakai bila ada: upload file, transaksi database, kalkulasi, penomoran, atau logika yang dipakai ulang. CRUD sederhana tanpa efek samping boleh langsung memakai Eloquent di controller.
- **Tanpa Repository pattern** (Eloquent sudah cukup; tambah lapisan hanya bila ada kebutuhan nyata).
- Nilai tetap (jenis transaksi, status) memakai **PHP backed enum** di `app/Enums/<Modul>/`.
- Integrasi antar modul (mis. pembayaran bootcamp → transaksi kas) melalui relasi polimorfik `referensi` di `tb_transaksi_kas` dan/atau Service, **bukan** dengan modul saling mengubah tabel modul lain.

### Struktur Folder
```
app/
├── Enums/{Kas,CompanyProfile}/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── Admin/DashboardController.php
│   │   ├── Admin/CompanyProfile/   (IdentitasController, HeroController, ...)
│   │   ├── Admin/Kas/              (AkunKasController, TransaksiKasController, ...)
│   │   └── Web/                    (frontend publik — Fase 5)
│   └── Requests/Admin/{CompanyProfile,Kas}/   (StoreXxxRequest, UpdateXxxRequest)
├── Models/
│   ├── Pengguna.php
│   ├── CompanyProfile/   (Identitas, Hero, Layanan, Portofolio, Artikel, KategoriArtikel, Faq, KontakKami)
│   └── Kas/              (AkunKas, KategoriTransaksi, TransaksiKas)
├── Services/{CompanyProfile,Kas,Shared}/
├── Support/              (helper murni: FormatRupiah, dsb.)
└── View/Components/Admin/
config/menu.php            (definisi menu sidebar per modul)
routes/
├── web.php                (rute publik + auth)
├── admin.php              (grup admin: prefix /admin, middleware web+auth, name admin.)
└── admin/{company-profile,kas}.php   (di-require oleh admin.php)
resources/views/
├── layouts/{admin,auth}.blade.php
├── components/admin/      (page-header, card, form-input, alert, delete-button, empty-state, ...)
├── auth/login.blade.php
├── admin/dashboard.blade.php
├── admin/company-profile/<entitas-kebab>/{index,create,edit,show}.blade.php
├── admin/kas/<entitas-kebab>/{index,create,edit,show}.blade.php
└── web/                   (Fase 5)
```
`routes/admin.php` didaftarkan di `bootstrap/app.php` (callback `then:` pada `withRouting`).

### Menambah Modul Baru (mis. Bootcamp)
1. Tambah entri di `docs/DATABASE.md` dan `docs/ROADMAP.md` → disetujui.
2. Buat namespace `Bootcamp` di Models, Controllers/Admin, Requests/Admin, Services, Enums.
3. Buat `routes/admin/bootcamp.php` dan entri menu di `config/menu.php`.
4. View di `resources/views/admin/bootcamp/`.
5. Test di `tests/Feature/Admin/Bootcamp/` dan `tests/Unit/Services/Bootcamp/`.

## 7. Konvensi Penamaan

**Database TIDAK mengikuti konvensi Laravel. Selain database, SEMUA mengikuti konvensi Laravel.**

| Hal | Konvensi | Contoh |
|---|---|---|
| Nama tabel | `tb_` + snake_case **tunggal** | `tb_pengguna`, `tb_kategori_artikel` |
| Primary key | `id_` + nama entitas | `id_pengguna`, `id_kategori_artikel` |
| Foreign key | sama persis dengan PK tabel rujukan | `id_kategori_artikel` |
| Kolom | snake_case | `nama_perusahaan`, `status_baca` |
| Model | PascalCase tunggal | `Pengguna`, `KategoriArtikel`, `KontakKami` |
| Controller | `<Model>Controller`, resource | `KategoriArtikelController` |
| Form Request | `Store<Model>Request`, `Update<Model>Request` | `StoreArtikelRequest` |
| Service | `<Model>Service` | `TransaksiKasService` |
| Enum | PascalCase, case PascalCase, value snake_case | `JenisTransaksi::Pemasukan` → `'pemasukan'` |
| Migration | `create_tb_<entitas>_table` | `2026_10_06_000001_create_tb_artikel_table.php` |
| URI | kebab-case Indonesia | `/admin/kategori-artikel` |
| Nama rute | `admin.<entitas-kebab>.<aksi>` | `admin.kategori-artikel.index` |
| View | kebab-case | `admin/company-profile/kategori-artikel/index.blade.php` |
| Blade component | kebab-case | `<x-admin.page-header>` |
| Variabel & method | camelCase | `$kategoriArtikel`, `hitungSaldo()` |
| Relasi | camelCase | `kategoriArtikel()`, `transaksiKas()` |

Tabel infrastruktur framework (`migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`) **tetap memakai nama default Laravel** karena nama & kolomnya dipakai internal oleh framework.

## 8. Pola Kode Wajib (karena PK/tabel non-standar)

### Model
```php
namespace App\Models\CompanyProfile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artikel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_artikel';
    protected $primaryKey = 'id_artikel';

    protected $fillable = ['id_kategori_artikel', 'judul', 'slug', 'deskripsi', 'gambar', 'tanggal', 'status_publikasi'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'status_publikasi' => \App\Enums\CompanyProfile\StatusPublikasi::class];
    }

    public function kategoriArtikel(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'id_kategori_artikel', 'id_kategori_artikel');
    }
}
```
- Selalu set `$table` dan `$primaryKey` secara eksplisit (pakai properti, bukan PHP Attributes, demi konsistensi).
- Selalu gunakan `$fillable` (jangan `$guarded = []`).
- **Setiap relasi wajib menyebut foreign key & owner/local key secara eksplisit.**

### Migration
```php
Schema::create('tb_artikel', function (Blueprint $table) {
    $table->id('id_artikel');
    $table->foreignId('id_kategori_artikel')
        ->constrained('tb_kategori_artikel', 'id_kategori_artikel')
        ->restrictOnDelete();
    $table->string('judul', 200);
    // ...
    $table->timestamps();
    $table->softDeletes();
});
```

### Jebakan yang Harus Dihindari
- Validasi `exists`/`unique` **wajib menyebut kolom**: `exists:tb_kategori_artikel,id_kategori_artikel`.
- Unique saat update: `Rule::unique('tb_artikel', 'slug')->ignoreModel($artikel)->withoutTrashed()` — `ignore($id)` tanpa nama kolom akan mencari kolom `id` dan salah.
- Jangan `orderBy('id')` / `pluck('name', 'id')` — gunakan nama kolom sebenarnya.
- Route model binding otomatis memakai `$primaryKey` — tidak perlu override `getRouteKeyName()` kecuali memakai slug di frontend publik.
- Kolom `user_id` di tabel `sessions` berisi nilai `id_pengguna` — ini normal.
- Factory untuk FK: `'id_kategori_artikel' => KategoriArtikel::factory()`.

## 9. Konvensi Fitur

### Controller & Rute
- Gunakan `Route::resource()` dan hanya aksi yang dibutuhkan (`->only()` / `->except()`).
- `index`: pencarian (`?q=`), filter, `->latest()->paginate(25)->withQueryString()` (25 data per halaman untuk semua modul — keputusan pemilik), eager loading relasi (cegah N+1).
- Setelah `store`/`update`/`destroy`: redirect dengan flash message (`->with('success', '...')`) yang ditampilkan sebagai toast.
- Hapus = soft delete, dengan konfirmasi modal. Hapus permanen hanya bila fitur "sampah" dibuat.
- **Semua aksi tambah, ubah, dan hapus wajib dikonfirmasi SweetAlert2** (keinginan pemilik). Hapus lewat `<x-admin.delete-button>`; form tambah/ubah diberi atribut `data-confirm="..."`, `data-confirm-title="..."`, `data-confirm-label="Ya, simpan"`, `data-confirm-variant="success"` (lihat `admin/kas/akun-kas/create|edit`).
- Warna tombol (pilihan pemilik): Tambah & Simpan `btn-success`, Ubah `btn-warning`, Hapus `<x-admin.delete-button>`, Batal `btn-secondary` dengan ikon `rotate-left` (termasuk tombol Batal dialog SweetAlert2).
- Modul singleton (`tb_identitas`) hanya punya `edit` & `update`.

### Upload File
- Gambar publik (company profile): disk `public`, folder `company-profile/<entitas>/`, nama file UUID. Validasi `image|mimes:jpg,jpeg,png,webp|max:2048`.
- **Bukti transaksi kas: disk `local` (privat)**, folder `kas/bukti/`, diakses hanya lewat rute ber-`auth` yang men-stream file. Validasi `mimes:jpg,jpeg,png,webp,pdf|max:5120`.
- Saat update dengan file baru, hapus file lama setelah penyimpanan sukses. File tetap disimpan saat soft delete.
- Logika upload dipusatkan di `App\Services\Shared\FileUploadService`.

### Tampilan
- Semua halaman admin extend `layouts/admin`. Pakai Blade component untuk elemen berulang (header halaman, kartu, input form + pesan error, tombol hapus, empty state).
  Komponen tersedia (anonymous, `resources/views/components/admin/`): `page-header`, `card` (slot `aksi`, `footer`, prop `flush`), `form-input`, `form-textarea`, `form-select` (`:options="[nilai => label]"`, atau `['Label grup' => [nilai => label]]` untuk `<optgroup>`), `form-file` (`berkas-saat-ini`), `form-switch` (boolean; `text`, `:checked`, mengirim hidden `0`), `filter-bar` (pencarian `?q=` + slot filter tambahan, diletakkan di atas tabel dalam `card :flush`), `alert`, `empty-state`, `delete-button` (modal konfirmasi via atribut `data-confirm`, dipasang `admin.js`), `pagination` (`:paginator`), `icon` (Font Awesome solid, `name` tanpa awalan `fa-`, mis. `<x-admin.icon name="gauge" />`; ikon menu di `config/menu.php` memakai nama yang sama). Semua ikon memakai Font Awesome — jangan menambah SVG inline. Flash `success`/`error`/`warning`/`info` otomatis tampil sebagai toast.
- Label, pesan, dan validasi dalam Bahasa Indonesia (`APP_LOCALE=id`, file `lang/id/validation.php`).
- Format: tanggal `translatedFormat('d F Y')` (zona `Asia/Jakarta`), uang `Rp 1.250.000` via `App\Support\FormatRupiah`.
- Input uang: `<x-admin.form-input ... data-rupiah inputmode="decimal" autocomplete="off">` (type teks, nilai awal mentah dari model). `admin.js` menampilkan titik ribuan saat diketik dan mengirim angka mentah (`1250000.5`) ke server, jadi validasi tetap `numeric|decimal:0,2`.
- Isian wajib (`:required="true"`) ditandai bintang **di depan** label: `*Nama Kategori` (pilihan pemilik).
- Daftar data: 25 baris per halaman.
- Tabel daftar: teks rata kiri, **nominal rata kanan** (judul & isi; pilihan pemilik), status di tengah. Nominal ditulis `<span class="cell-mono">{{ FormatRupiah::format(...) }}</span>` di `<th>`/`<td class="kolom-nominal">` (rata kanan, nowrap); nilai minus (saldo/selisih) diberi kelas `nominal-negatif` (merah). Kolom aksi memakai `<th class="kolom-aksi">` dan `<td class="kolom-aksi"><div class="aksi-tabel">…tombol…</div></td>` agar lebarnya pas dengan tombol (lihat `admin/kas/akun-kas/index`).
- Konten HTML (artikel) wajib disanitasi sebelum disimpan; tampilkan dengan `{!! !!}` **hanya** untuk konten yang sudah disanitasi. Selain itu selalu `{{ }}`.

## 10. Keamanan

- Tidak ada registrasi publik. Akun admin dibuat oleh `PenggunaSeeder` dari env `ADMIN_NAMA`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
- Login: rate limit (5 percobaan/menit per email+IP) — **dinonaktifkan saat `APP_ENV=local`** agar tidak mengganggu development, aktif di produksi. `session()->regenerate()` setelah login, invalidate saat logout.
- **Tidak ada fitur "ingat saya"** dan tidak ada kolom `remember_token` (lihat `docs/DATABASE.md` bagian A). Jangan menambahkannya.
- Semua rute `/admin/*` di bawah middleware `auth`. Untuk sekarang semua pengguna terautentikasi = admin; role & permission direncanakan (lihat ROADMAP).
- Form publik (kontak kami — Fase 5): rate limit + honeypot.
- Produksi: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS, cookie `secure`, header keamanan via middleware.
- Transaksi kas mencatat `created_by` & `updated_by` dan tidak pernah dihapus permanen dari UI.

## 11. ISO/IEC 25010 — Penerapan Praktis

| Karakteristik | Penerapan di proyek ini |
|---|---|
| Functional suitability | Feature test untuk setiap alur CRUD & aturan bisnis; perhitungan saldo diuji unit |
| Performance efficiency | Paginasi server-side, eager loading, indeks pada kolom filter/FK, cache konfigurasi/rute di produksi, gambar dibatasi ukurannya |
| Compatibility | Hanya fitur standar MySQL 8/PHP 8.3; modul berinteraksi lewat relasi/Service yang terdefinisi |
| Interaction capability (usability) | UI konsisten (komponen Blade), pesan validasi jelas berbahasa Indonesia, konfirmasi aksi destruktif, responsif, dark mode |
| Reliability | Transaksi DB untuk operasi multi-langkah, soft delete, backup database, halaman error 403/404/500 ramah |
| Security | Lihat §10; validasi seluruh input, escape output, file keuangan privat, audit `created_by/updated_by` |
| Maintainability | Struktur modular, konvensi ketat, controller tipis, Pint + Larastan + test wajib lulus sebelum commit, docs selalu diperbarui |
| Flexibility (portability) | Konfigurasi via `.env`, tanpa path absolut, deploy terdokumentasi & dapat diulang |
| Safety | Tidak ada hapus permanen data keuangan, `php artisan down` saat deploy, migrasi produksi hanya maju |

## 12. Definition of Done (per fitur)

- [ ] Migration, model (+ relasi eksplisit), factory, seeder (bila perlu) sesuai `docs/DATABASE.md`
- [ ] Form Request untuk store & update, pesan berbahasa Indonesia
- [ ] Controller, rute bernama, entri menu `config/menu.php`
- [ ] View memakai layout & komponen admin, responsif, tampil benar di mode terang & gelap
- [ ] Feature test: akses tamu ditolak, validasi, create/read/update/delete berhasil
- [ ] Unit test untuk setiap Service yang memuat logika
- [ ] `composer check` lulus tanpa error
- [ ] Catatan deploy (perintah server setelah `git pull`) disertakan
- [ ] Item terkait di `docs/ROADMAP.md` dicentang
