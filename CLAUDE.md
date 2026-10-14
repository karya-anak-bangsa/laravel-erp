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
- **Modul saat ini**: **Company Profile** — identitas, hero, layanan, portofolio, artikel + kategori, FAQ, kontak kami.
- **Modul masa depan** (desain hari ini harus siap menampungnya): riwayat pelatihan, sertifikasi, bootcamp, klien/proyek, invoice.
- **Fokus saat ini**: Fase 4 — frontend publik dengan dua tema (Full Color & Monochrome), dikerjakan bertahap (lihat `docs/ROADMAP.md`).
- **Standar kualitas**: ISO/IEC 25010 (lihat §11).
- **Domain produksi**: https://karyaanakbangsa.co.id (Hostinger)
- **Tahap produksi**: **uji coba** — seluruh isi produksi masih data dummy yang belum bernilai (keputusan pemilik 2026-10-11). Selama tahap ini berlaku aturan khusus di §2.4. Tahap ini berakhir hanya bila pemilik menyatakan produksi sudah memakai data real.
- **Repositori**: https://github.com/karya-anak-bangsa/laravel-erp

## 2. Aturan Kerja untuk Claude

1. **Bahasa**: berkomunikasi dalam Bahasa Indonesia. Komentar kode dalam Bahasa Indonesia, singkat, hanya untuk menjelaskan *mengapa*, bukan *apa*.
2. **Rencana dulu**: untuk fitur baru atau perubahan yang menyentuh lebih dari 2 file, tuliskan rencana singkat (daftar file yang dibuat/diubah + alasannya) dan tunggu persetujuan sebelum menulis kode.
3. **Patuhi skema**: jangan menambah/mengubah/menghapus kolom di luar `docs/DATABASE.md` tanpa persetujuan. Jika ada kebutuhan, usulkan dulu lalu perbarui `docs/DATABASE.md`.
4. **Migration, seeder, dan data produksi**:
   - **Seeder = cermin database lokal.** `CompanyProfileSeeder` (+ `PenggunaSeeder` untuk akun admin) harus selalu menghasilkan data yang sama persis dengan database lokal, termasuk berkas gambarnya (`database/seeders/berkas`, disalin lewat `MenyalinBerkasAwal`). Setiap perubahan data lokal yang harus ikut ke produksi dimasukkan ke seeder, bukan diketik ulang lewat admin produksi.
   - **Selama tahap uji** (§1): migration yang sudah jalan di produksi **boleh diedit atau disusun ulang** (tetap satu migration per tabel, sesuai `docs/DATABASE.md`), dan database produksi **boleh direset dari seeder berulang kali** (`migrate:fresh` + seeder, tanpa cadangan) lewat prosedur `docs/DEPLOY.md` › Reset Data Produksi. Reset juga wajib bila migration yang sudah jalan di produksi diedit.
   - **Setelah data real masuk** (pemilik yang menyatakan): migration yang sudah jalan di produksi tidak boleh diedit — buat migration baru untuk perubahan. Bila sebuah modul dibatalkan, file migration-nya boleh dihapus; bila migration itu sudah jalan di produksi, buang tabelnya lewat migration baru (`dropIfExists`, tabel anak lebih dulu) agar produksi tetap hanya bergerak maju. Perubahan data dilakukan lewat admin; reset tidak boleh lagi.
5. **Paket baru** (composer/npm) harus diminta izin dulu, sebutkan alasannya dan pastikan kompatibel dengan Laravel 13 / PHP 8.3.
6. **Git**: semua commit langsung ke `main` (developer tunggal). Deploy ke Hostinger dilakukan **sendiri oleh pemilik proyek** (Claude tidak melakukan deploy), sehingga `main` harus selalu dalam kondisi siap dipakai di produksi:
   - Jangan commit atau push kecuali diminta. Alurnya: Claude menyelesaikan pekerjaan, menjalankan `composer check`, lalu mengusulkan pesan commit; pemilik me-review, commit, push, dan deploy sendiri.
   - Sebelum commit, wajib lulus `composer check` (lint + analisis statis + test).
   - Format pesan: Conventional Commits berbahasa Indonesia, scope = modul.
     Contoh: `feat(company-profile): tambah CRUD kategori artikel`, `fix(company-profile): perbaiki validasi slug artikel`, `chore(deploy): perbarui workflow`.
   - Satu commit = satu perubahan logis.
7. **Rahasia**: jangan pernah menulis kredensial ke kode, docs, atau commit. `.env` tidak boleh di-commit.
8. **Selesai berarti memenuhi Definition of Done** (§12), lalu centang item terkait di `docs/ROADMAP.md`.
9. **Kecil dan sering online**: pemilik proyek ingin setiap kemajuan langsung bisa diakses di https://karyaanakbangsa.co.id. Pecah pekerjaan menjadi fitur kecil yang masing-masing utuh dan bisa di-deploy. Jangan meninggalkan `main` dalam kondisi setengah jadi (menu yang mengarah ke halaman rusak, migration tanpa fitur, dsb.).
10. **Catatan deploy**: di akhir setiap fitur, sebutkan singkat apa yang berdampak ke server: migration baru, seeder yang perlu dijalankan sekali, variabel `.env` baru, paket composer/npm baru, apakah aset perlu di-build ulang, dan (selama tahap uji) apakah **reset data produksi** diperlukan karena migration atau seeder berubah. Pisahkan langkah wajib dan opsional, masing-masing dengan alasan singkat.
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
| Editor WYSIWYG | TipTap 3 (`@tiptap/core`, `@tiptap/starter-kit`), npm; sanitasi HTML di server dengan `symfony/html-sanitizer` ^7.4 (versi 8 butuh PHP 8.4) |
| Testing | Pest (di atas PHPUnit) |
| Kualitas kode | Laravel Pint (preset `laravel`), Larastan |
| Frontend publik | **Tailwind CSS v4 + Basecoat UI** (`basecoat-css`, port shadcn/ui untuk HTML, MIT) dengan entri Vite terpisah dari aset admin; paket npm dipasang (dengan izin) di Fase 4 langkah 2. **Satu markup Blade, dua tema**: Full Color (bawaan) dan Monochrome, dipilih pengunjung lewat tombol di navbar. Kedua tema memakai font Geist + Geist Mono dan **ukuran yang identik** (Monochrome acuan; pilihan pemilik 2026-10-11) — bedanya hanya warna, dekorasi, dan susunan tertentu (§9). Warna, font, sudut, skala, lebar konten, ketebalan judul, dan bayangan tiap tema berasal dari template aktif per jenis di `tb_template` (Pengaturan Sistem › Manajemen Template), selalu dibaca lewat `TemaService` dengan cadangan `config/tema.php` bila database tidak terbaca. Prototipe acuan (di luar repo): `laravel-erp-prototipe/tema-ganda.html`. BootstrapMade dan Preline tidak dipakai (diputuskan pemilik 2026-10-09) |

### Integrasi Gentelella v4 dengan Laravel
- Pasang via npm, lalu impor SCSS & modul JS di `resources/scss/admin.scss` dan `resources/js/admin.js`; dikompilasi oleh Vite Laravel.
- Gentelella v4 menyuntikkan shell (sidebar/topbar) lewat JavaScript. Di proyek ini **sidebar, topbar, dan breadcrumb dirender server-side dengan Blade** (agar menu aktif, nama pengguna, dan otorisasi dikendalikan Laravel). Salin markup dari halaman referensi `production/*.html` dan `src/v4/shell-render.js`. `mountShell()` (di `resources/js/admin.js`) boleh dipanggil karena hanya memasang perilaku bila markup sudah ada; jangan pakai kelas `.tb-avatar`, `.tb-notifications`, `.tb-messages`, `.sidebar-user .more-btn`, `.theme-toggle`, dan kotak pencarian topbar karena Gentelella mengikatnya ke menu/data demo. **Panel admin hanya bermode terang** (mode gelap dihapus atas keputusan pemilik) — jangan memasang atribut `data-theme` atau toggle tema. Menu dropdown dibuat dengan tombol ber-atribut `data-menu="<id>"` + `<template id="<id>">` berisi markup Blade (`.menu-item`, `.menu-separator`); `admin.js` membukanya lewat `openPanel()`. Contoh: menu pengguna di `.sidebar-footer` (nama, email, logout); topbar hanya berisi tombol sidebar + breadcrumb. Layout admin terdiri dari `layouts/admin.blade.php` + `layouts/partials/{head,sidebar,topbar,footer}`; breadcrumb dikirim lewat `@extends('layouts.admin', ['breadcrumb' => ['Label' => url|null]])`.
- Komponen JS Gentelella (chart, menu `openPanel`) boleh dipakai langsung. **Toast memakai Simple Notify dan dialog konfirmasi memakai SweetAlert2** (pilihan pemilik) — jangan memakai `showToast`/`showModal` Gentelella. Keduanya dipasang di `resources/js/admin.js` (sumber: flash session & atribut `data-confirm`), CSS-nya di `admin.scss` dengan token Gentelella agar seragam dengan panel admin. Keamanan: `text` Simple Notify dirender sebagai HTML → selalu di-escape; di SweetAlert2 pakai `titleText`/`text`, bukan `title`/`html`, untuk isi yang memuat input pengguna.
- Jangan memakai kelas Bootstrap — Gentelella v4 tidak memuatnya. Gunakan kelas/komponen Gentelella; cek halaman *Component playground* untuk markup yang benar.
- Tabel daftar data memakai **paginasi & pencarian server-side Laravel**, bukan DataTables client-side (performa saat data membesar).

## 5. Perintah Umum

```bash
composer install && npm install        # instal dependensi
php artisan serve                       # server PHP di http://localhost:8000
npm run dev                             # Vite dev server (jalankan bersamaan dengan artisan serve)
npm run build                           # build aset produksi
php artisan migrate --seed              # migrasi + seeder
php artisan migrate:fresh --seed        # reset database lokal (produksi: hanya selama tahap uji, lewat prosedur docs/DEPLOY.md)
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
- Nilai tetap (jenis, status) memakai **PHP backed enum** di `app/Enums/<Modul>/`. Enum yang menjadi pilihan form punya `label()` dan trait `App\Enums\Concerns\MenyediakanOpsi` (`opsi()` = `[nilai => label]` untuk `<x-admin.form-select>`).
- Integrasi antar modul melalui relasi dan/atau Service yang terdefinisi, **bukan** dengan modul saling mengubah tabel modul lain.

### Struktur Folder
```
app/
├── Enums/CompanyProfile/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── Admin/DashboardController.php
│   │   ├── Admin/CompanyProfile/   (IdentitasController, HeroController, ...)
│   │   └── Web/CompanyProfile/     (BerandaController, KontakKamiController — frontend publik)
│   └── Requests/{Admin,Web}/CompanyProfile/   (StoreXxxRequest, UpdateXxxRequest)
├── Models/
│   ├── Pengguna.php
│   └── CompanyProfile/   (Identitas, Hero, Layanan, Portofolio, Artikel, KategoriArtikel, Faq, KontakKami)
├── Services/{CompanyProfile,Shared}/
├── Support/              (helper murni tanpa state)
└── View/Composers/       (MenuComposer; TemaComposer & SitusComposer untuk view publik; HalamanErrorComposer untuk errors::*)
config/menu.php            (definisi menu sidebar per modul)
config/tema.php            (dua template bawaan: data awal TemplateSeeder & cadangan; dibaca hanya lewat TemaService)
config/perusahaan.php      (nama cadangan perusahaan; titik peta kantor, env opsional PETA_LAT/PETA_LNG/PETA_ZOOM)
routes/
├── web.php                (rute publik + auth)
├── admin.php              (grup admin: prefix /admin, middleware web+auth, name admin.)
└── admin/company-profile.php   (di-require oleh admin.php; satu file per modul)
resources/css/web.css + resources/css/web/{token,struktur,komponen}.css   (Tailwind v4 + Basecoat, frontend publik)
resources/js/web.js + resources/js/web/*.js                               (perilaku frontend publik)
resources/views/
├── layouts/{admin,auth,web}.blade.php   (+ layouts/web/{head,aset,lompat-konten,navbar,footer,tombol-wa})
├── layouts/{error,web-error}.blade.php  (halaman error panel admin & publik)
├── errors/{403,404,419,429,4xx,500,503,5xx}.blade.php   (hanya judul & pesan, dipakai kedua layout error)
├── components/admin/      (page-header, card, form-input, alert, delete-button, empty-state, ...)
├── components/web/        (ikon, kepala-seksi, isian, tautan-luar)
├── auth/login.blade.php
├── admin/dashboard.blade.php
├── admin/company-profile/<entitas-kebab>/{index,create,edit,show}.blade.php
└── web/beranda.blade.php + web/beranda/*.blade.php   (seksi beranda publik)
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
| Service | `<Model>Service` | `ArtikelService` |
| Enum | PascalCase, case PascalCase, value snake_case | `StatusPublikasi::Terbit` → `'terbit'` |
| Migration | `create_tb_<entitas>_table` | `2026_10_06_000001_create_tb_artikel_table.php` |
| URI | kebab-case Indonesia | `/admin/kategori-artikel` |
| Nama rute | `admin.<entitas-kebab>.<aksi>` | `admin.kategori-artikel.index` |
| View | kebab-case | `admin/company-profile/kategori-artikel/index.blade.php` |
| Blade component | kebab-case | `<x-admin.page-header>` |
| Variabel & method | camelCase | `$kategoriArtikel`, `buatSlug()` |
| Relasi | camelCase | `kategoriArtikel()`, `artikel()` |

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
- Cast ditulis di method `casts()`; Larastan membacanya lewat `parseModelCastsMethod: true` di `phpstan.neon`, sehingga atribut ber-cast enum bertipe enum (bukan string) di analisis statis.
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
- `Route::resource()` untuk nama berakhiran *-s* (mis. `kelas`) wajib diberi `->parameters(['kelas' => 'kelas'])`. Tanpanya Laravel menganggap nama itu jamak bahasa Inggris dan memotong parameternya menjadi `{kela}`.
- Kolom `user_id` di tabel `sessions` berisi nilai `id_pengguna` — ini normal.
- Factory untuk FK: `'id_kategori_artikel' => KategoriArtikel::factory()`.

## 9. Konvensi Fitur

### Controller & Rute
- Gunakan `Route::resource()` dan hanya aksi yang dibutuhkan (`->only()` / `->except()`).
- `index`: pencarian (`?q=`), filter, `->latest()->paginate(25)->withQueryString()` (25 data per halaman untuk semua modul — keputusan pemilik), eager loading relasi (cegah N+1).
- Setelah `store`/`update`/`destroy`: redirect dengan flash message (`->with('success', '...')`) yang ditampilkan sebagai toast.
- Hapus = soft delete, dengan konfirmasi modal. Hapus permanen hanya bila fitur "sampah" dibuat.
- **Semua aksi tambah, ubah, dan hapus wajib dikonfirmasi SweetAlert2** (keinginan pemilik). Hapus lewat `<x-admin.delete-button>`; form tambah/ubah diberi atribut `data-confirm="..."`, `data-confirm-title="..."`, `data-confirm-label="Ya, simpan"`, `data-confirm-variant="success"`, mis. `<form method="POST" action="…" novalidate data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan kategori artikel baru?" data-confirm-label="Ya, simpan" data-confirm-variant="success">`.
- Warna tombol (pilihan pemilik): Tambah & Simpan `btn-success`, Lihat `btn-info` (ikon `eye`), Ubah `btn-warning`, Hapus `<x-admin.delete-button>`, Batal `btn-secondary` dengan ikon `rotate-left` (termasuk tombol Batal dialog SweetAlert2). Urutan tombol selalu aksi utama di kiri lalu Batal di kanan, di form maupun dialog SweetAlert2 (jangan pakai `reverseButtons`).
- Modul singleton (`tb_identitas`) hanya punya `edit` & `update`.

### Upload File
- Gambar publik (company profile): disk `public`, folder `company-profile/<entitas>/`, nama file UUID. Validasi `image|mimes:jpg,jpeg,png,webp|max:2048`.
- **Berkas privat** (dokumen internal yang tidak boleh publik, bila kelak dibutuhkan): disk `local`, folder `<modul>/<entitas>/`, nama file UUID, diakses hanya lewat rute ber-`auth` yang men-stream file.
- Saat update dengan file baru, hapus file lama setelah penyimpanan sukses. File tetap disimpan saat soft delete.
- Logika upload dipusatkan di `App\Services\Shared\FileUploadService`. Untuk simpan/ubah data berberkas pakai `simpanDenganBerkas()` (simpan berkas baru → jalankan penyimpanan → hapus berkas lama; berkas baru dibersihkan bila gagal). Berkas bawaan untuk seeder disalin lewat trait `Database\Seeders\Concerns\MenyalinBerkasAwal`.

### Tampilan
- Semua halaman admin extend `layouts/admin`. Pakai Blade component untuk elemen berulang (header halaman, kartu, input form + pesan error, tombol hapus, empty state).
- Susunan halaman (pilihan pemilik): `<x-admin.page-header>` hanya berisi **nama modul** (mis. `title="Company Profile"`, tanpa `pretitle` dan tanpa tombol) di semua halaman — daftar, tambah, ubah, singleton. Nama entitas & aksi (Hero › Tambah) cukup tampil di breadcrumb. Halaman daftar: page-header → `<x-admin.filter-bar>` (kartu sendiri) → `<x-admin.card title="<Entitas>" :flush="true">` dengan tombol Tambah di `<x-slot:aksi>` (judul kiri, tombol kanan) berisi tabel.
  Komponen tersedia (anonymous, `resources/views/components/admin/`): `page-header`, `card` (slot `aksi`, `footer`, prop `flush`), `form-input`, `form-textarea`, `form-editor` (WYSIWYG TipTap; `:maks` = batas teks terlihat + penghitung; tinggi 5 baris untuk semua editor — pilihan pemilik, jangan diberi `rows`; satu-satunya pengecualian isi artikel: `:baris="15"` dan `:judul-bagian="true"` untuk tombol sub-judul H2/H3, disanitasi dengan `bersihkanHtml([...], judulBagian: true)`), `form-select` (`:options="[nilai => label]"`, atau `['Label grup' => [nilai => label]]` untuk `<optgroup>`), `form-file` (`berkas-saat-ini`), `form-switch` (boolean; `text`, `:checked`, mengirim hidden `0`), `form-warna` (hex `#rrggbb` + pemilih warna browser yang saling mengisi lewat `resources/js/admin/template.js`), `filter-bar` (pencarian `?q=` + slot filter tambahan; merender kartunya sendiri, diletakkan di bawah page-header dan di atas kartu tabel), `alert`, `empty-state`, `delete-button` (modal konfirmasi via atribut `data-confirm`, dipasang `admin.js`; `:ikon-saja="true"`), `detail-button` (tombol Lihat; isi slot = rincian data dalam `<template>`, dibuka `admin.js` sebagai modal SweetAlert2; `title`, `:ikon-saja="true"`), `pagination` (`:paginator`), `icon` (Font Awesome solid, `name` tanpa awalan `fa-`, mis. `<x-admin.icon name="gauge" />`; ikon menu di `config/menu.php` memakai nama yang sama: Dashboard `landmark`, semua menu modul `book`; ikon di tombol Dashboard `landmark`, semua menu modul `book`) pencarian 12px, kecuali kompensasi optik `pen-to-square` 13px dan `rotate-left` 11px — diatur di `admin.scss`, jangan diberi ukuran per halaman). Semua ikon memakai Font Awesome — jangan menambah SVG inline. Flash `success`/`error`/`warning`/`info` otomatis tampil sebagai toast.
  Isian berulang (tambah/hapus baris, mis. keyword & CTA hero) memakai repeater `data-repeater` dari `resources/js/admin/repeater.js`; contoh markup di `admin/company-profile/hero/_form.blade.php`. Baris kosong dibuang dan diindeks ulang di `prepareForValidation()`.
- Label, pesan, dan validasi dalam Bahasa Indonesia (`APP_LOCALE=id`, file `lang/id/validation.php`).
- Format tanggal: `translatedFormat('d F Y')` (zona `Asia/Jakarta`). Halaman publik memakai `j F Y` tanpa nol di depan ("9 Oktober 2026", pilihan pemilik).
- Input tanggal: `type="date"` bawaan browser dengan nilai `Y-m-d` (pilihan pemilik). Format tampilannya mengikuti bahasa browser (mis. Chrome `yyyy-mm-dd`, Firefox `mm/dd/yyyy`) dan tidak bisa diatur dari kode. Pemilih tanggal JS (flatpickr) sudah dicoba dan dibatalkan karena tampilan kalendernya kurang serasi — jangan usulkan lagi kecuali diminta.
- Tabel yang hanya menampilkan informasi (mis. rincian laporan) diberi kelas `tabel-informasi` dan tanpa tautan per baris (pilihan pemilik).
- Isian wajib (`:required="true"`) ditandai bintang **di depan** label: `*Nama Kategori` (pilihan pemilik).
- Daftar data: 25 baris per halaman.
- Tabel daftar: teks rata kiri, status di tengah. Kolom aksi memakai `<th class="kolom-aksi">` dan `<td class="kolom-aksi"><div class="aksi-tabel">…tombol…</div></td>` agar lebarnya pas dengan tombol.
- Aksi per baris (pilihan pemilik): tiga tombol ikon persegi 32px tanpa teks — Lihat, Ubah, Hapus — setinggi tombol Tambah. Lihat = `<x-admin.detail-button title="Detail <Entitas>" :ikon-saja="true">`; Ubah = `<a class="btn btn-warning btn-ikon" title="Ubah" aria-label="Ubah">`; Hapus = `<x-admin.delete-button :ikon-saja="true">`. Contoh lengkap: `admin/company-profile/portofolio/index.blade.php`.
- Modal detail (pilihan pemilik) bergaya modal Bootstrap 5.3: judul kiri + tombol ×, footer Tutup rata kanan, lebar `.modal-lg` (800px mulai 992px, 500px mulai 576px). Isinya `<div class="detail-kolom">` berisi tabel rincian di kiri (`<div class="tabel-detail"><table class="table tabel-informasi">`, baris `<th scope="row">Label</th><td>Nilai</td>`, isian WYSIWYG ditampilkan `<td class="konten-html">{!! … !!}</td>`, nilai kosong `—`, ditutup baris Dibuat & Diperbarui `d F Y, H:i`) dan gambar di kanan (`pratinjau-gambar pratinjau-gambar-lebar`), rasio 8:4 sama dengan form tambah/ubah; bertumpuk di layar sempit.
- **WYSIWYG hanya untuk teks yang tampil di frontend** (prinsip pemilik): deskripsi hero, layanan (deskripsi & keterangan), portofolio, dan kelak isi artikel memakai `<x-admin.form-editor>`. Teks yang tidak tampil di frontend (mis. meta deskripsi, alamat identitas) tetap `<x-admin.form-textarea>`.
- Konten HTML wajib disanitasi sebelum disimpan: Form Request memakai trait `App\Http\Requests\Concerns\MembersihkanHtml` (`$this->bersihkanHtml([...])` di `prepareForValidation()`) dan aturan `new App\Rules\PanjangTeksHtml(<maks>)` sebagai pengganti `max:`. Tampilkan dengan `{!! !!}` **hanya** untuk konten yang sudah disanitasi (kelas `konten-html`); ringkasan di tabel memakai `Str::limit(App\Support\TeksHtml::polos(...))`. Selain itu selalu `{{ }}`. Data awal di seeder ditulis teks polos lalu dibungkus `TeksHtml::dariTeksPolos()`.

### Frontend publik (Fase 4)
- Layout `layouts/web` (bukan `layouts/admin`), entri Vite `resources/css/web.css` + `resources/js/web.js`. Satu markup, dua tema: `<html data-tema="full_color|monochrome">` dirender server dari cookie `tema` (ditulis `resources/js/web/tema.js`, dikecualikan dari enkripsi di `bootstrap/app.php`, divalidasi `JenisTemplate::dariCookie()`; bawaan Full Color).
- **Ukuran sama persis di kedua tema** (pilihan pemilik 2026-10-11): font, tinggi baris, spasi huruf, ketebalan, tinggi tombol/isian, padding, jarak, dan ukuran ikon ditulis SEKALI di aturan bersama tanpa `[data-tema]` di `resources/css/web/komponen.css` (di luar `@layer`), bernilai Monochrome sebagai acuan. `[data-tema=full_color] .kelas { … }` / `[data-tema=monochrome] .kelas { … }` hanya untuk warna, bayangan khusus satu tema, dekorasi, dan susunan yang memang beda. Beda yang dipertahankan: warna; bayangan lembut Full Color vs garis Monochrome (nilainya per tema dari pilihan bayangan template); dekorasi (blob/pendar hero, garis label seksi Full Color vs "01 /" Monochrome, titik di lencana kategori portofolio, garis merek footer, pola kisi, ilustrasi peta; pola titik di pojok kiri bawah & kanan atas bingkai hero dan titik lencana kata kunci hero tampil di kedua tema); Selengkapnya + modal layanan (Full Color) vs keterangan di kartu (Monochrome); kepala seksi rata tengah (Full Color) vs kiri (Monochrome); susunan artikel, FAQ, kotak ajakan, dan kartu pin peta. Di dalam susunan yang berbeda pun ukuran teksnya tetap sama. Kartu & bingkai bergaris `border` 1px di kedua tema (bukan `ring-1`, agar geometrinya sama); bingkai gambar Full Color memakai garis transparan + `background-clip: padding-box`. Beda kecil di markup memakai varian `warna:` / `mono:`, hanya untuk dekorasi/visibilitas, bukan ukuran. Cara memeriksa: alat `ukur-tema.mjs` + `beda-tema.mjs` (laravel-erp-prototipe/konsistensi-tema/alat) — laporan beda hanya boleh berisi hal di atas. Komponen bersudut wajib memakai token `--sudut-*` (`rounded-kartu`, `rounded-tombol`, dst.), bukan `rounded-full`; bulat hanya untuk bentuk dekoratif (titik, blob, contoh warna, pin peta).
- Nilai template (warna, nada, font, sudut, skala, lebar konten, ketebalan judul, bayangan) hanya lewat `App\Services\CompanyProfile\TemaService`, yang membaca template aktif per jenis dari `tb_template` dengan satu query per halaman (cadangan `config/tema.php` lalu nilai bawaan bila database gagal atau belum ada template aktif); view tidak membaca `config('tema')` langsung. Ukuran desktop navbar, logo, teks menu, tombol besar, dan judul seksi memakai token skala (`--tinggi-navbar`, `--tinggi-logo`, `--teks-menu`, `--tinggi-tombol`, `--teks-tombol`, `--teks-judul-seksi`; satu tabel untuk kedua jenis di `SkalaTemplate`), bukan angka tetap; batas lebar isi memakai `max-w-(--lebar-konten)`, bukan `max-w-7xl`. Bayangan permukaan kedua tema lewat token dari pilihan bayangan template (Fase 4 langkah 3b, pilihan pemilik 2026-10-14; nilai di `BayanganTemplate::token()`, dirender `TemaService`): `--bayangan-tombol`, `--bayangan-tombol-garis`, `--bayangan-isian`, `--bayangan-lencana`, `--bayangan-kartu`, `--bayangan-formulir`, `--bayangan-gambar`, khusus Full Color `--bayangan-kartu-sorot`, `--bayangan-navbar`, `--bayangan-ajakan`, `--bayangan-ikon`, plus `--garis-kartu` Monochrome (lebih gelap saat Tanpa). Aturannya ditulis sekali di aturan bersama `komponen.css` — jangan memakai utilitas `shadow-*` Tailwind untuk permukaan, dan jangan `shadow-lg`/`shadow-xl`/`shadow-2xl` di Full Color. Nilai Tanpa `0 0 #0000` (bukan `none`) agar sah di dalam `--tw-shadow`; warnanya hex beralfa yang dibulatkan ke 1/255 seperti keluaran build CSS, sehingga Halus identik dengan tampilan 2026-10-11 (dijaga `BayanganTemplateTest`). Elemen melayang (menu ponsel, modal layanan, tombol WhatsApp, popup & penanda peta, kartu pin, tautan lompat konten) dan penanda tema terpilih tidak ikut pilihan; Full Color memakai `--bayangan-melayang` statis di `token.css`. Elemen bergaris `border` memakai `box-shadow: var(--bayangan-…)` langsung; elemen ber-`ring-1` (item FAQ, kartu pin peta, gambar modal) serta tombol garis & isian Basecoat mengisinya lewat `--tw-shadow` agar garis/cincin fokusnya tidak tertimpa. Turunan kontras dihitung `App\Support\KontrasWarna`. Font publik di `vite.config.js` (`preload: false`): semua pilihan `FontTemplate` dibangun dengan bobot 400–800 (dijaga `FontTemplateTest`); font baru wajib ditambahkan di enum dan di sana bersamaan. Geist Mono dipakai kedua tema untuk label kecil (eyebrow hero, label seksi, lencana, nomor layanan, tanggal artikel, label kontak, nama di kartu peta, judul kolom footer, petunjuk geser).
- Identitas untuk frontend dibaca `IdentitasService::untukSitus()` (cache tanpa batas, dibuang event model `Identitas`), disediakan `SitusComposer` sebagai `$identitas` untuk `layouts.web*` dan `web.*`.
- Halaman error (Fase 4 langkah 2b): view `errors/*` hanya berisi `title` & `message`; `HalamanErrorComposer` memilih layout dari alamat — `admin`, `admin/*`, `login`, `logout` → `layouts/error` (Gentelella), selain itu → `layouts/web-error` — dan menyediakan `$kodeError`. Tombol per kode diatur di layout, bukan di view. Versi publik (pilihan pemilik 2026-10-14): navbar + footer + tombol WhatsApp seperti halaman lain, isi rata tengah di kedua tema (angka kode besar, judul, pesan), tombol "Ke beranda" + "Kembali" (`resources/js/web/kembali.js`, tampil hanya bila tab punya halaman sebelumnya); 503 hanya "Muat ulang". Halaman error tidak boleh bergantung pada sesi/`csrf_token()` (juga dirender untuk alamat tak dikenal dan mode pemeliharaan); identitas & layanan footer dibaca composer dengan `try/catch` — bila gagal, tampil ringkas (nama dari `config('perusahaan.nama')`, tanpa navbar/footer). Kode 429 wajib punya view sendiri karena view 429 bawaan Laravel menimpa cadangan `4xx`.
- Manajemen Template (Fase 4 langkah 3a, pilihan pemilik 2026-10-14): satu template aktif per jenis dijaga `TemplateService` (pola Hero); template aktif tidak bisa dihapus (tombol Hapus nonaktif, controller juga menolak), dinonaktifkan, atau diganti jenisnya (`UpdateTemplateRequest`); aktivasi lewat switch di form; daftar tetap tiga tombol aksi. Isian berpasangan per kelompok — Jenis + Pengaturan, Font + Ketebalan Judul, Sudut + Bayangan, Skala + Lebar Konten — dan modal Lihat memakai urutan yang sama. Pengaturan Default/Custom (pilihan pemilik 2026-10-14, menggantikan usulan tombol "kembalikan bawaan"): Default mengunci isian warna/nada sampai lebar konten lewat `<fieldset disabled>` dan mengisinya dengan nilai bawaan `config/tema.php` (`TemaService::nilaiBawaan()`; diisi `resources/js/admin/template.js` di form, dan `StoreTemplateRequest` di server karena isian terkunci tidak terkirim); tidak disimpan sebagai kolom — template dianggap Default bila semua nilai tampilannya sama dengan bawaan (`TemaService::pengaturan()`). Nada dasar Monochrome: Netral, Biru, Hijau, Ungu, Cokelat — kecerahan Tailwind neutral + kekuatan tinta Tailwind slate agar setiap pilihan terlihat, teks redup tetap ≥ 4,5:1 (dijaga `NadaDasarTest`); jangan kembali ke skala abu Tailwind lain (zinc, stone, dst.) karena bedanya dengan Netral nyaris tak terlihat. Pratinjau (`admin.template.pratinjau`, dari tombol Pratinjau di form dan modal Lihat) merender beranda asli di tab baru dengan nilai dari query string tanpa menyimpan: controller menaruh `Template` di atribut request `TemaService::PRATINJAU` pada request asli (FormRequest hanyalah salinan), dibaca `TemaComposer`; pita `.pita-pratinjau` di `layouts/web` hanya tampil di mode ini. Judul besar (hero, seksi, band ajakan, halaman error) memakai `font-weight: var(--ketebalan-judul)` dari template, bukan `font-semibold`; judul kartu tetap semibold.
- Ikon frontend: SVG Lucide lewat `<x-web.ikon nama="…">` (aturan Font Awesome di atas khusus panel admin). Tautan ke situs lain: `<x-web.tautan-luar>`.
- Tautan menu memakai `{{ route('beranda') }}#seksi` agar tetap berfungsi dari halaman publik lain. Jangan menautkan ke halaman yang belum ada (mis. kartu artikel belum bertautan sampai halaman artikel dibuat).
- Tombol berteks di frontend tanpa ikon (pilihan pemilik 2026-10-10/11: navbar, hero, Selengkapnya layanan); ikon hanya untuk tombol ikon-saja (menu ponsel, tema). Layanan: dua teratas menurut `urutan_ke` menjadi kartu lebar di kedua tema. Portofolio tanpa tab filter kategori (keputusan pemilik 2026-10-11: data nyata didominasi website & mobile apps); kartu berurutan menurut `Portofolio::KATEGORI_UTAMA` (Website, Mobile Apps, Pelatihan IT, Sertifikasi IT, Bootcamp, dicocokkan lewat slug), dalam satu kategori yang terbaru lebih dulu. Ukuran teks kartu di layanan, portofolio, dan artikel sama antarseksi dan di kedua tema: judul 18px, deskripsi/ringkasan 15px/1.65, lencana & tanggal Geist Mono 13px. Gambar kartu layanan biasa & portofolio 16:10; di ponsel (<640px) kartu layanan kedua tema memakai thumbnail di kanan judul. Hero dua kolom mulai 768px dengan gambar dalam bingkai berjarak di kedua tema. Ringkasan kartu artikel maksimal 150 karakter termasuk spasi, dipotong di akhir kata lewat `TeksHtml::potongDiAkhirKata()` (tanpa `line-clamp` agar tampil utuh). Artikel Full Color: tanggal "Diterbitkan …" di bawah ringkasan (kartu samping di bawah 1024px hanya tanggal karena kolomnya tidak muat), gambar kartu samping rata atas (6rem di ponsel, tablet selebar mungkin dengan kolom teks 14.25rem). Band ajakan hanya tombol WhatsApp ("Kirim Pesan" ke `#kontak` hanya cadangan bila link WhatsApp kosong). Kontak: susunan sama di kedua tema (kiri atas daftar kontak satu kartu bergaris pemisah, kiri bawah peta, kanan formulir); semua baris kontak tautan berpanah dan setinggi sama (alamat → Google Maps); ikon kontak Full Color berwarna padat dengan ikon putih, teksnya digeser 1px ke atas sebagai koreksi optik garis bawah permanen; kartu telepon hanya cadangan bila link WhatsApp kosong; peta Leaflet + OpenStreetMap dengan popup nama perusahaan + "Buka di Google Maps" (URL dari koordinat `config/perusahaan.php`). Footer: kontak WhatsApp tampil sebagai logo WhatsApp + nomor (telepon hanya cadangan bila link WhatsApp kosong). Menu navbar rata kanan (tepat sebelum pilihan tema). Tombol WhatsApp melayang selalu tampil, berbentuk pil berlabel di kedua tema (Full Color hijau `#15803D`, Monochrome hitam); footer diberi ruang bawah (`body:has(#tombol-wa) .kaki-isi`) agar tombol tidak menutupi isinya. Tautan menu bergulir halus lewat `html { scroll-behavior: smooth }` (bukan `html:focus-within`, karena klik mouse tidak memberi fokus), dimatikan bila `prefers-reduced-motion: reduce`.
- Seksi beranda yang datanya kosong tidak dirender; teks WYSIWYG ditampilkan `{!! !!}` di `.konten-html`, ringkasan memakai `TeksHtml::polos()`. Ringkasan kartu dari isi bersub-judul (artikel) memakai `TeksHtml::ringkasan()` agar teks H2/H3 tidak menyambung dengan paragraf.
- Ponsel sempit (360–389px, pilihan pemilik 2026-10-11): blok `@media (max-width: 389.98px)` di akhir `komponen.css` merapatkan ruang dalam kartu dan sedikit memperkecil judul agar jumlah baris judul & label sama dengan di 390px; nilainya satu set untuk kedua tema (aturan `[data-tema]` di blok itu hanya untuk kotak khusus satu tema); 390px ke atas tidak tersentuh. Teks yang bisa menyisakan satu kata di baris terakhir diberi `text-pretty` atau `whitespace-nowrap` pada frasa penutupnya (mis. "Hak cipta dilindungi.").
- Kelas Tailwind hanya dipindai dari berkas publik (`@source` di `web.css`), jadi **setiap perubahan Blade/JS publik wajib `npm run build`** sebelum deploy.

## 10. Keamanan

- Tidak ada registrasi publik. Akun admin dibuat oleh `PenggunaSeeder` dari env `ADMIN_NAMA`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
- Login: rate limit (5 percobaan/menit per email+IP) — **dinonaktifkan saat `APP_ENV=local`** agar tidak mengganggu development, aktif di produksi. `session()->regenerate()` setelah login, invalidate saat logout.
- **Tidak ada fitur "ingat saya"** dan tidak ada kolom `remember_token` (lihat `docs/DATABASE.md` bagian A). Jangan menambahkannya.
- Semua rute `/admin/*` di bawah middleware `auth`. Untuk sekarang semua pengguna terautentikasi = admin; role & permission direncanakan (lihat ROADMAP).
- Form publik (kontak kami): `throttle:kontak` 3 kiriman/menit per IP (dimatikan saat `APP_ENV=local` seperti login) + honeypot `website` (pesan bot tidak disimpan tetapi tetap dibalas sukses). Sesi habis (419) dan batas kiriman kembali ke formulir dengan isian utuh.
- Produksi: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS, cookie `secure`, header keamanan via middleware.

## 11. ISO/IEC 25010 — Penerapan Praktis

| Karakteristik | Penerapan di proyek ini |
|---|---|
| Functional suitability | Feature test untuk setiap alur CRUD & aturan bisnis; logika Service diuji unit |
| Performance efficiency | Paginasi server-side, eager loading, indeks pada kolom filter/FK, cache konfigurasi/rute di produksi, gambar dibatasi ukurannya |
| Compatibility | Hanya fitur standar MySQL 8/PHP 8.3; modul berinteraksi lewat relasi/Service yang terdefinisi |
| Interaction capability (usability) | UI konsisten (komponen Blade), pesan validasi jelas berbahasa Indonesia, konfirmasi aksi destruktif, responsif |
| Reliability | Transaksi DB untuk operasi multi-langkah, soft delete, backup database, halaman error 403/404/500 ramah |
| Security | Lihat §10; validasi seluruh input, escape output, berkas internal privat |
| Maintainability | Struktur modular, konvensi ketat, controller tipis, Pint + Larastan + test wajib lulus sebelum commit, docs selalu diperbarui |
| Flexibility (portability) | Konfigurasi via `.env`, tanpa path absolut, deploy terdokumentasi & dapat diulang |
| Safety | Soft delete (hapus permanen hanya lewat fitur "sampah", lihat §9), `php artisan down` saat deploy, migrasi produksi hanya maju setelah tahap uji berakhir (§2.4) |

## 12. Definition of Done (per fitur)

- [ ] Migration, model (+ relasi eksplisit), factory, seeder (bila perlu) sesuai `docs/DATABASE.md`; seeder menghasilkan data yang sama dengan database lokal (§2.4)
- [ ] Form Request untuk store & update, pesan berbahasa Indonesia
- [ ] Controller, rute bernama, entri menu `config/menu.php`
- [ ] View memakai layout & komponen admin, responsif (desktop, tablet, ponsel)
- [ ] Frontend publik: `npm run build`, lalu cek visual kedua tema (Full Color & Monochrome) di 1536, 390 & 360 px
- [ ] Feature test: akses tamu ditolak, validasi, create/read/update/delete berhasil
- [ ] Unit test untuk setiap Service yang memuat logika
- [ ] `composer check` lulus tanpa error
- [ ] Catatan deploy (perintah server setelah `git pull`, termasuk perlu/tidaknya reset data produksi selama tahap uji) disertakan, lalu usulan pesan commit
- [ ] Item terkait di `docs/ROADMAP.md` dicentang
