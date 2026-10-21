# CLAUDE.md — ERP PT. Teknologi Karya Anak Bangsa (TKAB)

Panduan utama untuk Claude Code di repositori ini. Baca seluruhnya di awal sesi; dokumen lain dibaca saat relevan:

| File | Baca ketika |
|---|---|
| `docs/DATABASE.md` | Membuat/mengubah migration, model, factory, seeder, relasi, validasi `exists`/`unique` |
| `docs/ROADMAP.md` | Memulai sesi, memilih tugas berikutnya, atau menandai progres |
| `docs/DEPLOY.md` | Menyusun catatan deploy, menambah paket/perintah yang harus jalan di server Hostinger |

---

## 1. Tentang Proyek

- **Perusahaan**: PT. Teknologi Karya Anak Bangsa — jasa pembuatan website, mobile apps, pelatihan IT, sertifikasi IT, dan bootcamp mahasiswa.
- **Produk**: sistem ERP berbasis web yang dikembangkan bertahap. Modul saat ini **Company Profile** (identitas, hero, layanan, portofolio, artikel + kategori, tutorial + materi, FAQ, template tampilan) beserta frontend publiknya. Modul masa depan yang harus bisa ditampung desain hari ini: pelatihan, sertifikasi, bootcamp, klien/proyek, invoice. Status dan urutan pekerjaan ada di `docs/ROADMAP.md`.
- **Prinsip**: kualitas mendekati ISO/IEC 25010 (§11) **tanpa over engineering**. Lapisan, paket, pilihan konfigurasi, atau test baru hanya untuk kebutuhan nyata; pekerjaan dan pemeriksaan sebatas yang diminta dan yang terdampak.
- **Produksi**: https://karyaanakbangsa.co.id (Hostinger), masih **tahap uji**: seluruh isinya data dummy yang belum bernilai, dengan aturan khusus di §2.4. Tahap ini berakhir hanya bila pemilik menyatakan produksi sudah memakai data real.
- **Repositori**: https://github.com/karya-anak-bangsa/laravel-erp

## 2. Aturan Kerja untuk Claude

1. **Bahasa**: berkomunikasi dalam Bahasa Indonesia. Komentar kode dalam Bahasa Indonesia, singkat, hanya untuk menjelaskan *mengapa*, bukan *apa*.
2. **Rencana dulu**: untuk fitur baru atau perubahan yang menyentuh lebih dari 2 file, tuliskan rencana singkat (daftar file yang dibuat/diubah + alasannya) dan tunggu persetujuan sebelum menulis kode.
3. **Patuhi skema**: jangan menambah/mengubah/menghapus kolom di luar `docs/DATABASE.md` tanpa persetujuan. Bila ada kebutuhan, usulkan dulu lalu perbarui `docs/DATABASE.md`.
4. **Migration, seeder, dan data produksi**:
   - **Seeder = cermin database lokal.** `CompanyProfileSeeder` (+ `PenggunaSeeder` untuk akun admin) selalu menghasilkan data yang sama persis dengan database lokal, termasuk berkas gambarnya (`database/seeders/berkas`, disalin lewat `MenyalinBerkasAwal`). Pengecualian: isi `tb_log_aktivitas`, yang berupa catatan kejadian. Perubahan data lokal yang harus ikut ke produksi dimasukkan ke seeder, bukan diketik ulang lewat admin produksi.
   - **Selama tahap uji**: migration yang sudah jalan di produksi boleh diedit atau disusun ulang (tetap satu migration per tabel), dan database produksi boleh direset dari seeder berulang kali tanpa cadangan lewat `docs/DEPLOY.md` › Reset Data Produksi. Reset wajib bila migration yang sudah jalan di produksi diedit atau dihapus.
   - **Setelah data real masuk**: migration yang sudah jalan di produksi tidak boleh diedit; perubahan lewat migration baru. Modul yang dibatalkan dibuang lewat migration baru (`dropIfExists`, tabel anak lebih dulu) bila migration-nya sudah jalan di produksi. Perubahan data lewat admin; reset tidak boleh lagi.
5. **Paket baru** (composer/npm) harus diminta izin dulu, dengan alasan, dan kompatibel dengan Laravel 13 / PHP 8.3.
6. **Git**: semua commit langsung ke `main` (developer tunggal). Pemilik yang me-review, commit, push, dan deploy ke Hostinger; Claude tidak melakukan deploy, jadi `main` harus selalu siap produksi.
   - Jangan commit atau push kecuali diminta. Claude menyelesaikan pekerjaan, menjalankan `composer check` (lint + analisis statis + test) bila kode berubah, lalu mengusulkan pesan commit.
   - Format: Conventional Commits berbahasa Indonesia, scope = modul, mis. `feat(company-profile): tambah CRUD kategori artikel`, `fix(company-profile): perbaiki validasi slug artikel`, `docs: …`. Satu commit = satu perubahan logis.
7. **Rahasia**: jangan pernah menulis kredensial ke kode, docs, atau commit. `.env` tidak boleh di-commit.
8. **Selesai** berarti memenuhi Definition of Done (§12), lalu centang item terkait di `docs/ROADMAP.md`.
9. **Kecil dan sering online**: setiap kemajuan sebaiknya langsung bisa diakses di produksi. Pecah pekerjaan menjadi fitur kecil yang masing-masing utuh dan bisa di-deploy; jangan meninggalkan `main` setengah jadi (menu ke halaman rusak, migration tanpa fitur, dsb.).
10. **Catatan deploy**: di akhir setiap fitur, sebutkan yang berdampak ke server: migration baru, seeder yang perlu dijalankan sekali, variabel `.env` baru, paket composer/npm baru, perlu tidaknya build aset, dan (selama tahap uji) perlu tidaknya **reset data produksi**. Pisahkan langkah wajib dan opsional, masing-masing dengan alasan singkat.
11. Jika instruksi pengguna bertentangan dengan file ini, ikuti pengguna lalu tawarkan untuk memperbarui file ini.
12. **Dokumen**: tulis aturan yang berlaku sekarang, singkat, dan di satu tempat (tabel di atas; README hanya pengantar & instalasi). Riwayat dan alasan lama cukup tersimpan di git. File ini dibaca di setiap sesi, jadi detail yang sudah dijaga kode, test, atau acuan tampilan tidak perlu ditulis di sini.

## 3. Lingkungan Pengembangan

- **OS**: Windows dengan **Laragon 8.4.0** — Apache 2.4.62, PHP 8.3.28, MySQL 8.0.40, Node.js 24.12, Git 2.47.1, Composer 2.10.1.
- **Proyek**: `C:\laragon\www\project\laravel-erp`, dijalankan dengan `php artisan serve` → `http://localhost:8000` (Laragon hanya dipakai untuk MySQL).
- **Editor**: VS Code + Claude Code. Tulis perintah terminal yang berjalan di Git Bash maupun PowerShell (hindari `sudo` dan `&&` pada PowerShell lama).
- **Layar acuan**: laptop 14 inci 1920×1200 berskala 125% (layar CSS 1536×960; area tampil Chrome ±1536×790 dengan bilah bookmark, dipakai untuk menilai tampilan yang bergantung pada tinggi layar); tampilan wajib tetap responsif di tablet & ponsel.
- **Database**: MySQL `laravel_erp` (user `root`, tanpa password); test memakai `laravel_erp_testing`. Proyek wajib MySQL (`DB_CONNECTION=mysql`), bukan SQLite bawaan skeleton.
- **Cakupan test**: ekstensi PCOV 1.0.12 (`php_pcov.dll` build TS VS16 x64 dari downloads.php.net) di folder `ext` PHP Laragon, tidak diaktifkan di `php.ini` dan hanya dimuat `composer coverage`. Bila versi PHP Laragon diganti, salin ulang DLL yang cocok.

## 4. Tech Stack

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 13 (PHP ≥ 8.3) |
| Database | MySQL 8, charset `utf8mb4_unicode_ci` |
| Panel admin | **Gentelella v4** (npm `gentelella`) — vanilla JS + SCSS + Vite, grafik ECharts. **Tanpa Bootstrap, tanpa jQuery.** |
| Toast & konfirmasi | Simple Notify (toast) dan SweetAlert2 (dialog konfirmasi), npm |
| Editor WYSIWYG | TipTap 3 (`@tiptap/core`, `@tiptap/starter-kit`); sanitasi HTML di server dengan `symfony/html-sanitizer` ^7.4 (versi 8 butuh PHP 8.4) |
| Frontend publik | **Tailwind CSS v4 + Basecoat UI** (`basecoat-css`) dengan entri Vite terpisah dari admin; ikon Lucide. Satu markup, dua tema (§9 › Frontend Publik) |
| Build aset | Vite (laravel-vite-plugin) |
| Testing & kualitas | Pest (di atas PHPUnit), Laravel Pint (preset `laravel`), Larastan |

### Integrasi Gentelella v4
- SCSS & modul JS diimpor di `resources/scss/admin.scss` dan `resources/js/admin.js`, dikompilasi Vite.
- Sidebar, topbar, dan breadcrumb **dirender server-side dengan Blade** agar menu aktif, nama pengguna, dan otorisasi dikendalikan Laravel; markup disalin dari `production/*.html` dan `src/v4/shell-render.js` Gentelella. `mountShell()` boleh dipanggil karena hanya memasang perilaku pada markup yang sudah ada. Jangan memakai `.tb-avatar`, `.tb-notifications`, `.tb-messages`, `.sidebar-user .more-btn`, `.theme-toggle`, dan kotak pencarian topbar karena terikat ke data demo.
- Panel admin **hanya bermode terang**: jangan memasang atribut `data-theme` atau toggle tema.
- Menu dropdown: tombol `data-menu="<id>"` + `<template id="<id>">` berisi `.menu-item`/`.menu-separator`, dibuka `admin.js` lewat `openPanel()`. Menu pengguna (nama, email, logout) ada di `.sidebar-footer`; topbar hanya berisi tombol sidebar + breadcrumb.
- Layout: `layouts/admin.blade.php` + `layouts/partials/{head,sidebar,topbar,footer}`; breadcrumb lewat `@extends('layouts.admin', ['breadcrumb' => ['Label' => url|null]])`.
- Komponen JS Gentelella (chart, `openPanel`) boleh dipakai. Toast memakai Simple Notify dan konfirmasi SweetAlert2 (dipasang di `admin.js` dari flash session & atribut `data-confirm`; CSS di `admin.scss` dengan token Gentelella); jangan memakai `showToast`/`showModal` Gentelella. Keamanan: `text` Simple Notify dirender sebagai HTML → selalu di-escape; di SweetAlert2 pakai `titleText`/`text`, bukan `title`/`html`, untuk isi yang memuat input pengguna.
- Jangan memakai kelas Bootstrap; gunakan kelas Gentelella (cek halaman *Component playground*).
- Tabel daftar memakai **paginasi & pencarian server-side Laravel**, bukan DataTables client-side.

## 5. Perintah Umum

```bash
composer install && npm install        # instal dependensi
php artisan serve                       # server PHP di http://localhost:8000
npm run dev                             # Vite dev server (bersamaan dengan artisan serve)
npm run build                           # build aset produksi
php artisan migrate --seed              # migrasi + seeder
php artisan migrate:fresh --seed        # reset database lokal (produksi: hanya selama tahap uji, lewat docs/DEPLOY.md)
php artisan test                        # jalankan test
composer lint                           # vendor/bin/pint
composer analyse                        # vendor/bin/phpstan analyse
composer check                          # pint --test + phpstan + test (wajib sebelum commit kode)
composer coverage                       # test + laporan cakupan baris per berkas (PCOV, §3)
```

## 6. Arsitektur

### Prinsip
- **Modular per domain** di dalam struktur standar Laravel (tanpa paket modul eksternal). Setiap modul punya namespace sendiri di setiap lapisan; menambah modul = menambah folder namespace baru tanpa mengubah modul lain.
- **Controller tipis**: menerima request → memanggil Model/Service → mengembalikan view/redirect.
- **Validasi selalu di Form Request**, tidak pernah di controller.
- **Service** dipakai bila ada upload file, transaksi database, kalkulasi, penomoran, atau logika yang dipakai ulang. CRUD sederhana tanpa efek samping boleh langsung memakai Eloquent di controller.
- **Tanpa Repository pattern**: Eloquent sudah cukup.
- Nilai tetap (jenis, status) memakai **PHP backed enum** di `app/Enums/<Modul>/`. Enum pilihan form punya `label()` dan trait `App\Enums\Concerns\MenyediakanOpsi` (`opsi()` = `[nilai => label]` untuk `<x-admin.form-select>`).
- Integrasi antarmodul lewat relasi dan/atau Service yang terdefinisi, **bukan** dengan modul saling mengubah tabel modul lain.

### Struktur Folder
```
app/Enums/<Modul>/
app/Http/Controllers/{Auth,Admin/<Modul>,Web/<Modul>}/   (+ Admin/DashboardController, Web/SeoController)
app/Http/Requests/{Admin,Web}/<Modul>/
app/Models/{<Modul>,Concerns}/     (Pengguna langsung di app/Models; Concerns = trait model bersama)
app/Services/{<Modul>,Shared}/
app/Support/                       (helper murni tanpa state)
app/View/Composers/                (menu, tema, situs, SEO, halaman error)
config/{menu,tema,perusahaan,seo}.php
routes/web.php, routes/admin.php   (prefix /admin, middleware web+auth, name admin.; didaftarkan di bootstrap/app.php)
routes/admin/<modul>.php           (di-require admin.php; satu file per modul)
resources/{scss/admin.scss,js/admin.js,js/admin/}             (panel admin)
resources/{css/web.css,css/web/,js/web.js,js/web/}             (frontend publik)
resources/views/{layouts,errors,components/{admin,web},admin/<modul>/<entitas-kebab>,web,seo,vendor/pagination}/
```

### Menambah Modul Baru (mis. Bootcamp)
1. Tambah entri di `docs/DATABASE.md` dan `docs/ROADMAP.md` → disetujui.
2. Buat namespace `Bootcamp` di Models, Controllers/Admin, Requests/Admin, Services, Enums. Model yang diubah lewat admin memakai trait `App\Models\Concerns\MencatatAktivitas` (konstanta `LABEL_LOG` & `ISIAN_LOG` berlabel seperti form, method `namaUntukLog()`) dan didaftarkan di morph map `AppServiceProvider` (§10 › Log Aktivitas).
3. Buat `routes/admin/bootcamp.php` dan entri menu di `config/menu.php` (bila khusus peran tertentu: Gate + `can:` di rute + `izin` di grup menu, §10).
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
| Model | PascalCase tunggal | `Pengguna`, `KategoriArtikel`, `MateriTutorial` |
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

Tabel infrastruktur framework (`migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`) **tetap memakai nama default Laravel** karena dipakai internal oleh framework.

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
- Selalu set `$table` dan `$primaryKey` secara eksplisit (properti, bukan PHP Attributes).
- Cast ditulis di method `casts()`; Larastan membacanya lewat `parseModelCastsMethod: true` di `phpstan.neon`, sehingga atribut ber-cast enum bertipe enum di analisis statis.
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
- Unique saat update: `Rule::unique('tb_artikel', 'slug')->ignoreModel($artikel)->withoutTrashed()`; `ignore($id)` tanpa nama kolom akan mencari kolom `id` dan salah.
- Jangan `orderBy('id')` / `pluck('name', 'id')`; gunakan nama kolom sebenarnya.
- Route model binding otomatis memakai `$primaryKey`; override `getRouteKeyName()` hanya bila memakai slug di frontend publik.
- `Route::resource()` untuk nama berakhiran *-s* (mis. `kelas`) wajib diberi `->parameters(['kelas' => 'kelas'])`; tanpanya Laravel memotong parameternya menjadi `{kela}`.
- Kolom `user_id` di tabel `sessions` berisi nilai `id_pengguna`; ini normal.
- Pengguna yang sedang login di controller: parameter `#[CurrentUser] Pengguna $pengguna`. `$request->user()` bertipe nullable sehingga ditolak Larastan bila langsung dipakai.
- Factory untuk FK: `'id_kategori_artikel' => KategoriArtikel::factory()`.

## 9. Konvensi Fitur

### Controller & Rute
- Gunakan `Route::resource()` dengan aksi yang dibutuhkan saja (`->only()` / `->except()`). Modul singleton (`tb_identitas`) hanya punya `edit` & `update`.
- `index`: pencarian (`?q=`), filter, eager loading relasi (cegah N+1), `->paginate(25)->withQueryString()`. Urutan `->latest()`, kecuali data master (mis. kategori artikel) yang diurutkan abjad.
- Setelah `store`/`update`/`destroy`: redirect dengan flash message (`->with('success', '...')`) yang tampil sebagai toast.
- Hapus = soft delete. Hapus permanen hanya bila fitur "sampah" dibuat.
- **Semua aksi tambah, ubah, dan hapus dikonfirmasi SweetAlert2.** Hapus lewat `<x-admin.delete-button>`; form tambah/ubah diberi `data-confirm="Pastikan data sudah benar sebelum disimpan."`, `data-confirm-title="Simpan <entitas> baru?"`, `data-confirm-label="Ya, simpan"`, `data-confirm-variant="success"`. Setelah dikonfirmasi, tombol submit dinonaktifkan sampai halaman berpindah (`admin.js`) agar data tidak terkirim dua kali.
- Tombol: Tambah & Simpan `btn-success`, Lihat `btn-info` (ikon `eye`), Ubah `btn-warning`, Hapus `<x-admin.delete-button>`, Batal `btn-secondary` berikon `rotate-left` (termasuk Batal di SweetAlert2). Aksi utama di kiri, Batal di kanan, di form maupun dialog (tanpa `reverseButtons`). Kartu filter: Terapkan `btn-secondary` (ikon `magnifying-glass`) dan Reset `btn-danger` (ikon `clock-rotate-left`), sama tinggi & lebar dalam `.aksi-filter`. Tombol tambah selalu `<x-admin.tombol-tambah>`.

### Upload File
- Gambar publik: disk `public`, folder `company-profile/<entitas>/`, nama file UUID, validasi `image|mimes:jpg,jpeg,png,webp|max:2048`. Gambar konten (hero, layanan, portofolio, artikel) disimpan dengan `optimalkanGambar: true`: diperkecil sampai sisi terpanjang 1600 px dan dikodekan WebP; logo & favicon identitas tidak.
- Berkas privat (bila kelak dibutuhkan): disk `local`, folder `<modul>/<entitas>/`, nama file UUID, diakses hanya lewat rute ber-`auth` yang men-stream file.
- Logika upload di `App\Services\Shared\FileUploadService`. Simpan/ubah data berberkas memakai `simpanDenganBerkas()`: simpan berkas baru → simpan data → hapus berkas lama; berkas baru dibersihkan bila gagal. File tetap disimpan saat soft delete. Berkas bawaan seeder disalin lewat trait `Database\Seeders\Concerns\MenyalinBerkasAwal`.

### Panel Admin
- Semua halaman extend `layouts/admin` dan memakai komponen di `resources/views/components/admin/` (page-header, card, form-*, filter-bar, tombol-tambah, alert, empty-state, delete-button, detail-button, pagination, icon, statistik). `form-select` menerima `[nilai => label]` atau `['Label grup' => [nilai => label]]` untuk `<optgroup>`; `filter-bar` merender kartunya sendiri; `detail-button` membuka rincian dalam `<template>` sebagai modal SweetAlert2.
- Susunan halaman: `<x-admin.page-header>` hanya berisi nama modul (mis. `title="Company Profile"`) di semua halaman; entitas & aksi cukup di breadcrumb. Halaman daftar: page-header → `<x-admin.filter-bar>` → `<x-admin.card title="<label menu sidebar>" :flush="true">` (mis. `Daftar Tutorial`, `Kategori Artikel`, `FAQ`) dengan `<x-admin.tombol-tambah :href="…">` di `<x-slot:aksi>`, berisi tabel. Daftar kosong cukup `<x-admin.empty-state>` tanpa tombol tambah.
- Tabel: padding sel 8px 16px; semua kolom rata kiri, kecuali status (`kolom-status`) dan jumlah (`kolom-jumlah`, selebar judulnya) di tengah. Status (Terbit/Draf, Aktif/Nonaktif, dsb.) berupa badge Gentelella `<span class="chip chip-green|chip-yellow">`: hijau = tampil/aktif, kuning = perlu tindakan (draf, belum ada materi, tidak diindeks), merah = Nonaktif (pilihan pemilik). Peran pengguna juga badge, rata kiri di kolom biasa: ungu Super Administrator, biru Administrator (`PeranPengguna::kelasChip()`), karena hijau, kuning, dan merah sudah bermakna status. Kolom pendek lain (gambar, urutan, kategori/induk, tanggal) diberi `kolom-pas` (selebar isinya) pada `th` & `td`, sehingga sisa lebar jatuh ke kolom judul + ringkasan, atau ke kolom data terakhir bila tabel tanpa judul (template) agar kolom data rapat di kiri. Tabel kategori: kolom jumlah diikuti kolom pengisi kosong `class="kolom-sisa" aria-hidden="true"` sebelum Aksi. Ringkasan di bawah judul `<div class="card-subtitle ringkasan-tabel">` (maks. 2 baris, `Str::limit(…, 400)`; isi bersub-judul lewat `TeksHtml::ringkasan()`). Kolom aksi `<th class="kolom-aksi">` + `<td class="kolom-aksi"><div class="aksi-tabel">…</div></td>` (kepala di tengah, tombol rata kanan) berisi tiga tombol ikon persegi 32px: Lihat (`<x-admin.detail-button title="Detail <Entitas>" :ikon-saja="true">`), Ubah (`<a class="btn btn-warning btn-ikon" title="Ubah" aria-label="Ubah">`), Hapus (`<x-admin.delete-button :ikon-saja="true">`). Contoh lengkap: `admin/company-profile/portofolio/index.blade.php`. Tabel yang hanya menampilkan informasi diberi kelas `tabel-informasi`, tanpa tautan per baris.
- Modal detail: `<div class="detail-kolom">` berisi tabel rincian di kiri (`<div class="tabel-detail"><table class="table tabel-informasi">`, baris `<th scope="row">Label</th><td>Nilai</td>`, isian WYSIWYG `<td class="konten-html">{!! … !!}</td>`, nilai kosong `—`, diakhiri baris Dibuat & Diperbarui `d F Y, H:i`) dan gambar di kanan (`pratinjau-gambar pratinjau-gambar-lebar`); tinggi baris mengikuti isinya.
- Form: isian wajib (`:required="true"`) bertanda bintang di depan label (`*Nama Kategori`). Input tanggal `type="date"` bernilai `Y-m-d`. Isian berulang memakai repeater `data-repeater` (`resources/js/admin/repeater.js`, contoh `admin/company-profile/hero/_form.blade.php`); baris kosong dibuang dan diindeks ulang di `prepareForValidation()`, dan view membuang baris kosong yang sama dari `old()` (berisi kiriman mentah) agar pesan galat jatuh di baris yang benar. Repeater berkas (contoh `portofolio/_baris-gambar.blade.php`): berkas lama dikirim sebagai daftar id yang dipertahankan, baris tanpa berkas bernilai null dan dilewati.
- Ikon: Font Awesome solid lewat `<x-admin.icon name="…">` (tanpa awalan `fa-`); menu Dashboard `landmark`, menu Pengaturan Sistem `gear`, menu modul lain `book`. Ukuran ikon dan koreksi optiknya diatur sekali di `admin.scss`, bukan per halaman. Jangan menambah SVG inline di panel admin.
- Flash `success`/`error`/`warning`/`info` otomatis tampil sebagai toast.
- Dashboard (`admin/dashboard`) berisi ringkasan dari tabel yang sudah ada, tanpa tabel riwayat/audit log dan tanpa daftar konten yang belum tampil (pilihan pemilik): angka per modul `<x-admin.statistik>`, Terakhir diperbarui (dari `updated_at`), dan Status website, disediakan `App\Services\CompanyProfile\RingkasanService`. Modul baru menambah ringkasannya sendiri.

### Teks, WYSIWYG & Bahasa
- Label, pesan, dan validasi dalam Bahasa Indonesia (`APP_LOCALE=id`, `lang/id/validation.php`). Di panel admin status publikasi disebut Draf/Terbit dan akibatnya "tampil (di website)"; kata "tayang" tidak dipakai di teks admin (scope `tayang()` di kode tetap). Tanggal `translatedFormat('d F Y')` (zona `Asia/Jakarta`); halaman publik `j F Y` ("9 Oktober 2026").
- **WYSIWYG hanya untuk teks yang tampil di frontend** (hero, layanan, portofolio, jawaban FAQ, isi artikel) lewat `<x-admin.form-editor>`: tinggi minimum 8 baris (editor yang menjadi isian terakhir kartu memanjang mengisi sisa tinggi kartu), `:maks` = batas teks terlihat + penghitung. Pengecualian: isi artikel dan isi materi `:layar-penuh="true"` (tinggi pas layar agar form beserta tombol Simpan muat tanpa gulir, minimal 15 baris, ditambah tombol Layar penuh: kertas di ruang kerja, bilah jejak halaman, dan penghitung karakter) dan `:judul-bagian="true"` (sub-judul H2/H3), deskripsi portofolio `:baris="15"`. Teks yang tidak tampil di frontend (mis. meta deskripsi, alamat identitas) tetap `<x-admin.form-textarea>`.
- Konten HTML wajib disanitasi sebelum disimpan: Form Request memakai trait `App\Http\Requests\Concerns\MembersihkanHtml` (`$this->bersihkanHtml([...])` di `prepareForValidation()`; isi artikel dengan `judulBagian: true`) dan aturan `new App\Rules\PanjangTeksHtml(<maks>)` sebagai pengganti `max:`. `{!! !!}` **hanya** untuk konten tersanitasi (kelas `konten-html`); ringkasan memakai `App\Support\TeksHtml` (`polos()`, `ringkasan()` untuk isi bersub-judul, `potongDiAkhirKata()`). Selain itu selalu `{{ }}`.
- Data awal seeder ditulis teks polos lalu dibungkus `TeksHtml::dariTeksPolos()`, kecuali isi artikel contoh: satu berkas HTML per artikel di `database/seeders/data/artikel/<slug>.html` (satu elemen blok per baris; hanya p, h2, h3, ul/ol dengan `<li><p>`, strong, em, a) yang disaring `HtmlSanitizerService` seperti isian form. Isi materi tutorial contoh sama, di `database/seeders/data/tutorial/<slug-tutorial>/<slug-materi>.html`, ditambah `code` dan `<pre><code>` (isi `<pre>` tidak dirapatkan).
- Teks situs memakai bahasa baku sesuai EYD V: kalimat efektif dan aktif; kalimat tidak diawali konjungsi intrakalimat (dan, tetapi, sedangkan, sehingga, lalu), "Untuk", atau "Bagi" (anak kalimat "Saat …,"/"Jika …," dan konjungsi antarkalimat seperti "Oleh karena itu," boleh); tanpa koma sebelum "sehingga" dan "karena"; istilah asing miring (`<em>`) di setiap kemunculan dan kepanjangan singkatan ditulis "*Total Graphics Power* (TGP)", sedangkan nama diri, singkatan, kode, dan label menu tetap tegak; padanan lazim didahulukan (daring, perangkat lunak, peramban, berkas, galat), sedangkan padanan resmi yang belum lazim tidak dipakai (email, mouse, disk; bukan surel, tetikus, diska). Judul artikel atau tutorial yang disebut dalam kalimat ditulis tegak persis seperti di situs dan dijadikan tautan ke halamannya tanpa tanda petik; tanda petik hanya untuk judul yang tidak ditautkan.

### Frontend Publik
- Layout `layouts/web` (bukan `layouts/admin`), entri Vite `resources/css/web.css` + `resources/js/web.js`. Kelas Tailwind hanya dipindai dari berkas publik (`@source` di `web.css`), jadi **setiap perubahan Blade/JS/CSS publik wajib `npm run build`** sebelum deploy. Basecoat diimpor selektif di `resources/css/web/basecoat.css`; komponen Basecoat yang belum ada di sana ditambahkan dulu sebelum dipakai.
- **Dua tema, satu markup**: `<html data-tema="full_color|monochrome">` dirender server dari cookie `tema` (ditulis `resources/js/web/tema.js`, dikecualikan dari enkripsi di `bootstrap/app.php`, divalidasi `JenisTemplate::dariCookie()`; bawaan Full Color).
- **Monochrome = Full Color kecuali warna** (pilihan pemilik 2026-10-19): susunan, ukuran, komponen, dan efek ditulis sekali di aturan bersama tanpa `[data-tema]` di `resources/css/web/{struktur,komponen}.css` (di luar `@layer`). `[data-tema=…] .kelas` hanya untuk (1) warna: palet, dekorasi berwarna khusus Full Color (gradasi latar, blob, pendar, garis merek footer, warna per item), dan garis bawah sebagai penanda sorot/aktif Monochrome pengganti warna aksen; (2) band ajakan & kartu Konsultasi: Full Color kotak bergradasi berhias, Monochrome band gelap polos yang menyambung ke footer di halaman artikel & portofolio. Varian `warna:`/`mono:` di markup hanya untuk dekorasi berwarna. Penyesuaian layar sempit (<390px) ditulis di blok `@media (max-width: 389.98px)` di akhir `komponen.css`, satu set untuk kedua tema. Pemeriksa: `ukur-tema.mjs` + `beda-tema.mjs` (laporan per halaman di `laravel-erp-prototipe/acuan/beda-tema/`), yang hanya boleh berisi beda jenis di atas.
- **Nilai template** (warna, nada dasar, font, sudut, skala, lebar konten, ketebalan judul, bayangan) hanya lewat `App\Services\CompanyProfile\TemaService`: template aktif per jenis dari `tb_template` dengan satu query per halaman, cadangan `config/tema.php` lalu nilai bawaan bila database gagal. View tidak membaca `config('tema')` langsung. CSS memakai token, bukan angka atau utilitas tetap: sudut `--sudut-*` (`rounded-kartu`, `rounded-tombol`, dst.; bulat hanya untuk bentuk dekoratif), skala (`--tinggi-navbar`, `--tinggi-logo`, `--teks-menu`, `--tinggi-tombol`, `--teks-tombol`, `--teks-judul-seksi`), `max-w-(--lebar-konten)`, `font-weight: var(--ketebalan-judul)` untuk judul besar, dan bayangan `--bayangan-*` (nilai di `BayanganTemplate::token()`; jangan utilitas `shadow-*` Tailwind untuk permukaan). Elemen bergaris `border` memakai `box-shadow: var(--bayangan-…)`; elemen ber-`ring-1` serta tombol garis & isian Basecoat mengisinya lewat `--tw-shadow`. Turunan kontras dihitung `App\Support\KontrasWarna`. Font publik dibangun di `vite.config.js` (bobot 400–800); font baru ditambahkan di enum `FontTemplate` dan di sana bersamaan.
- **Manajemen Template**: satu template aktif per jenis dijaga `TemplateService`; template aktif tidak bisa dihapus, dinonaktifkan, atau diganti jenisnya. Pengaturan Default/Custom bukan kolom: Default mengunci isian dan memakai `TemaService::nilaiBawaan()` (isian terkunci tidak terkirim, jadi `StoreTemplateRequest` mengisinya di server); template dianggap Default bila semua nilainya sama dengan bawaan (`TemaService::pengaturan()`). Pratinjau (`admin.template.pratinjau`) merender beranda dengan nilai dari query string tanpa menyimpan, lewat atribut `TemaService::PRATINJAU` pada request asli (bukan salinan FormRequest) yang dibaca `TemaComposer`.
- **Manajemen Modul** (`tb_modul_website`, enum `JenisModul`; Hero tidak termasuk dan selalu pertama) hanya mengatur tampil/tidak dan urutan menu navbar, Tautan Cepat footer (`$menuUtama` dari `SitusComposer`), dan seksi beranda (pilihan pemilik): halaman modul, sitemap, kolom samping, dan band ajakan di halaman lain tidak ikut diatur. Satu urutan untuk menu dan seksi (`ModulWebsiteService::urutanTampil()`, cache dibuang event model, dibaca sekali per request). Selama seksi Layanan nonaktif, kolom Layanan footer & kotak Layanan kami tampil tanpa tautan. Latar putih/redup seksi beranda dihitung dari seksi yang tampil, dan tombol hero ke seksi yang tidak dirender dibuang. Modul publik baru yang punya menu atau seksi beranda menambah case `JenisModul`.
- **Identitas** dibaca `IdentitasService::untukSitus()` (cache tanpa batas, dibuang event model `Identitas`), disediakan `SitusComposer` sebagai `$identitas` untuk `layouts.web*` dan `web.*`. Seksi beranda yang datanya kosong tidak dirender. Pengunjung menghubungi perusahaan lewat link WhatsApp Identitas (tombol melayang, CTA hero, band ajakan, kartu Konsultasi, FAQ); bila link kosong, semuanya disembunyikan.
- **Halaman error**: view `errors/*` hanya berisi `title` & `message`; `HalamanErrorComposer` memilih layout dari alamat (`admin`, `admin/*`, `login`, `logout` → `layouts/error`; lainnya → `layouts/web-error`) dan menyediakan `$kodeError`; tombol per kode diatur di layout. Halaman error tidak boleh bergantung pada sesi/`csrf_token()` karena juga dirender untuk alamat tak dikenal dan mode pemeliharaan; identitas & layanan footer dibaca dengan `try/catch` dan halaman tampil ringkas bila gagal. Kode 429 wajib punya view sendiri karena view 429 bawaan Laravel menimpa cadangan `4xx`.
- **Ikon & tautan**: SVG Lucide lewat `<x-web.ikon nama="…">` (logo WhatsApp, Facebook, LinkedIn dari Simple Icons); tautan ke situs lain lewat `<x-web.tautan-luar>`. Tombol berteks tanpa ikon; ikon hanya untuk tombol ikon-saja. Menu ke seksi beranda memakai `{{ route('beranda') }}#seksi` agar berfungsi dari halaman lain. Jangan menautkan ke halaman yang belum ada (mis. `/layanan`). Hindari satu kata tersisa di baris terakhir (`text-pretty` atau `whitespace-nowrap` pada frasa penutup).
- **Artikel**: daftar `/artikel` (10 per halaman, `ArtikelController::PER_HALAMAN`, `<x-web.paginasi>`), kategori `/artikel/kategori/{slug}` (`Web\CompanyProfile\KategoriArtikelController`, susunan sama dengan daftar lewat partial `web/artikel/daftar`), dan detail `/artikel/{slug}`. Draf, terhapus, slug salah, kategori tanpa artikel terbit, dan nomor halaman di luar jangkauan → 404. Slug artikel dikunci setelah terbit (`ArtikelService::perbarui()`); slug kategori dibuat `KategoriArtikelService` dari nama dan dikunci begitu kategori punya artikel terbit (`KategoriArtikel::slugTerkunci()`). Kartu artikel satu komponen `<x-web.kartu-artikel>`; halaman publik selain beranda memakai `<x-web.kepala-halaman>`; kolom samping lewat partial `web/artikel/samping` (kotak Konsultasi & Layanan kami di `web/samping/*`, dipakai bersama portofolio).
- **Portofolio**: daftar `/portofolio` (10 per halaman, `PortofolioController::PER_HALAMAN`, terbaru lebih dulu, tanpa filter kategori) dan detail `/portofolio/{slug}` bersusunan sama dengan artikel (kelas detail artikel dipakai bersama). Terhapus, slug salah, dan nomor halaman di luar jangkauan → 404. Slug dikunci sejak dibuat (`PortofolioService::perbarui()`). Kartu beranda & daftar satu komponen `<x-web.kartu-portofolio>`; kolom samping `web/portofolio/samping`. Kategori tampil sebagai pita `.pita-kategori` (sama dengan pita artikel). Detail menampilkan gambar utama + gambar tambahan (`tb_gambar_portofolio`, total maks. `StorePortofolioRequest::MAKS_GAMBAR`) dalam carousel `resources/js/web/carousel.js`; mulai 1024px titik diganti miniatur semua gambar di dalam bingkai, di bawah gambar utama.
- **Tutorial**: materi promosi sederhana, bukan materi Pelatihan IT/Sertifikasi IT/Bootcamp (SKKNI, modul sendiri kelak). Daftar `/tutorial` (10 per halaman, `TutorialController::PER_HALAMAN`, terbaru lebih dulu), kategori `/tutorial/kategori/{slug}` (`Web\CompanyProfile\KategoriTutorialController`, susunan sama dengan daftar lewat partial `web/tutorial/daftar`; rutenya sebelum rute materi dan slug tutorial `kategori` dicadangkan), halaman tutorial `/tutorial/{slug}` (sampul berpita kategori `tb_kategori_tutorial`, pengantar, daftar materi, tombol "Mulai belajar"), dan materi `/tutorial/{slug}/{slug-materi}` (`scopeBindings()`; `Tutorial::childRouteBindingRelationshipName()` mengarahkannya ke relasi `materiTutorial`). Hanya tutorial terbit yang punya materi yang tayang (scope `tayang`); draf, tanpa materi, terhapus, slug salah, materi tutorial lain, kategori tanpa tutorial tayang, dan nomor halaman di luar jangkauan → 404. Nomor materi ("Materi N dari M") dan materi sebelumnya/berikutnya dihitung dari urutan tampil. Slug kategori dibuat `KategoriTutorialService` dari nama dan dikunci begitu kategori punya tutorial tayang. Kartu `<x-web.kartu-tutorial>` memakai kelas kartu artikel (jumlah materi di tempat tanggal) dan halaman tutorial memakai kelas detail artikel; kolom samping `web/tutorial/samping`: Konsultasi → Layanan kami → Kategori, sedangkan di halaman materi Daftar materi → Konsultasi → Layanan kami. Materi tanpa gambar, jadi gambar bagikannya sampul tutorial. Tanpa seksi beranda.
- **SEO**: judul, deskripsi, kanonis, robots, Open Graph, dan Twitter Card dirender `layouts/web/seo` dari `SeoComposer`. Halaman cukup mengisi section `judul` (tanpa akhiran nama perusahaan; tanpa section = Judul Website, hanya untuk beranda), `deskripsi`, `kanonis` (isi sendiri bila memakai slug atau paginasi), `jenis-og`, dan `gambar-bagikan`; tag khusus lewat `@push('kepala')`, mis. JSON-LD `<x-web.data-terstruktur>`. Semua alamat untuk mesin pencari (kanonis, og:url, og:image, sitemap, JSON-LD, tombol bagikan) dibentuk `App\Support\Seo::alamat()` dari `APP_URL`; `www.` dialihkan 301 ke tanpa www di `public/.htaccess`. `config('seo.indeks')` (env `SEO_INDEKS`, produksi `false` selama tahap uji) mengganti robots menjadi `noindex` dan membuang baris Sitemap dari robots.txt; pratinjau template, panel admin, login, dan halaman error selalu noindex. `/sitemap.xml` dan `/robots.txt` dirender `Web\SeoController`; jangan membuat `public/robots.txt` karena berkas statis didahulukan server. Modul baru yang punya halaman publik menambahkan alamatnya ke `seo/sitemap.blade.php`.

### Acuan Tampilan
- Potret situs (frontend publik kedua tema dan panel admin) di `laravel-erp-prototipe/acuan/` (di luar repo; buka `index.html`) adalah acuan tampilan. Potret dibuat `alat/potret-acuan.mjs` dari daftar `alat/daftar-acuan.json` dengan data seeder; cara pakai di `laravel-erp-prototipe/README.md`.
- Setiap perubahan tampilan dicek dengan `node alat/banding-acuan.mjs` dari folder itu (laporan di `banding/laporan.md`). Yang berubah hanya boleh bagian yang diminta; beda lain adalah regresi dan diperbaiki dulu.
- Cakupan pemeriksaan sebanding dengan perubahannya. Dari diff, tentukan halaman yang benar-benar menampilkan apa yang berubah, lalu batasi kedua alat dengan `--saring=` (cocok sebagian dengan nama potret, mis. `--saring=beranda,artikel` = seksi artikel beranda + halaman artikel publik & admin), `--lebar=`, atau `--bagian=web|admin`.
  - Logika, test, atau docs saja → tanpa potret.
  - Isi/data seeder saja (teks, gambar) → tanpa banding karena bedanya pasti hanya di isi itu; potret ulang halaman yang memuat data itu, lalu lihat satu atau dua potretnya (1536 & 390). Identitas dan layanan tampil di navbar/footer sehingga cakupannya semua halaman publik.
  - Tampilan satu modul → modul itu di semua lebar, ditambah beranda bila modulnya punya seksi di sana.
  - Isi bagian bersama yang tampil sama di setiap halaman (menu sidebar, navbar, footer) → banding cukup di halaman yang isinya ikut berubah, atau satu halaman perwakilan, pada lebar tempat bagian itu terlihat; halaman yang tidak memuatnya (login, error) dilewati. Acuan halaman lain yang memuatnya diperbarui sekaligus dengan `--perbarui` tanpa banding, di latar belakang.
  - Seluruh situs hanya bila yang berubah memengaruhi susunan atau gaya banyak halaman: kerangka layout, komponen Blade bersama, `token.css`/`komponen.css`/`admin.scss`, `TemaService`, atau paket npm.
- Acuan selalu mengikuti tampilan terbaru: setelah banding bersih, jalankan `node alat/potret-acuan.mjs --perbarui` dengan saringan yang sama (sekaligus mengosongkan `banding/`). Halaman atau modul baru ditambahkan dulu ke `daftar-acuan.json`.

### Jangan Diusulkan Ulang
Sudah dicoba atau ditolak pemilik; usulkan lagi hanya bila pemilik memintanya.
- Bootstrap, jQuery, DataTables client-side, atau mode gelap di panel admin; Repository pattern.
- Pemilih tanggal JS (mis. flatpickr): tampilan kalendernya kurang serasi.
- Tinggi baris modal detail yang dipaksa seragam.
- BootstrapMade atau Preline untuk frontend publik.
- Tab filter kategori di seksi portofolio: data nyata didominasi website & mobile apps.
- Tombol "Lihat semua artikel" di beranda (menu Artikel di navbar sudah mewakili).
- Kartu Konsultasi gratis yang menempel saat digulir: kotak lain ikut bergulir di baliknya.
- Nada dasar Monochrome dari skala abu Tailwind lain (zinc, stone, dst.): bedanya dengan Netral nyaris tak terlihat.
- JSON-LD FAQPage (hasil kaya FAQ kini hanya untuk situs pemerintah/kesehatan), Meta Keyword di halaman (diabaikan Google), dan favicon WEBP (tidak ditampilkan Google Search).
- Formulir kontak dan kotak masuk pesan (modul Kontak Kami dihapus 2026-10-20): kontak cukup lewat WhatsApp.
- Pengguna: kolom `status_aktif` (soft delete sudah memblokir login), email akun nonaktif yang boleh dipakai lagi, peran & izin dinamis atau paket permission (cukup dua peran tetap), serta isian kata sandi saat ini di Profil Saya (dihapus 2026-10-21).

## 10. Keamanan

- Tidak ada registrasi publik. Akun admin pertama dibuat `PenggunaSeeder` dari env `ADMIN_NAMA`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`; akun lain dibuat di Pengaturan Sistem › Manajemen Pengguna (kata sandi min. 8 karakter; akun yang sedang dipakai tidak bisa dihapus; akun yang dihapus otomatis keluar karena soft delete, tetap tercantum di daftar berstatus Nonaktif, dan bisa diaktifkan kembali dengan email & kata sandi lamanya). Akun sendiri hanya diubah lewat menu pengguna › Profil Saya, yang tidak memuat isian peran (tombol Ubah di baris akun sendiri dinonaktifkan); mengganti email atau kata sandi tidak meminta kata sandi saat ini, dan setelah disimpan pengguna kembali ke Dashboard (pilihan pemilik).
- Login: rate limit 5 percobaan/menit per email+IP (dimatikan saat `APP_ENV=local`), `session()->regenerate()` setelah login, invalidate saat logout.
- **Tidak ada fitur "ingat saya"** dan tidak ada kolom `remember_token` (`docs/DATABASE.md` bagian A). Jangan menambahkannya.
- **Tidak ada fitur lupa kata sandi** (pilihan pemilik): email perusahaan untuk SMTP tidak dibeli karena perusahaan masih merintis sehingga email pribadi masih cukup, dan email perusahaan membutuhkan biaya tambahan. Kata sandi yang lupa diganti Super Administrator lewat Manajemen Pengguna › Ubah; Super Administrator yang lupa ditangani lewat `docs/DEPLOY.md` › Aturan & Pemecahan Masalah. Jangan menambahkannya.
- Semua rute `/admin/*` di bawah middleware `auth` + `auth.session`: begitu kata sandi diganti, sesi akun itu di perangkat lain berakhir.
- Dua peran tetap (enum `PeranPengguna`, kolom `peran`): **Super Administrator** membuka semua menu; **Administrator** hanya Dashboard, Company Profile, dan Profil Saya. Pengaturan Sistem dijaga Gate `kelola-sistem` lewat middleware `can:kelola-sistem` di rutenya dan kunci `izin` di grup `config/menu.php`; menu yang disembunyikan tetap harus dijaga rutenya. Peran sendiri tidak bisa diubah, sehingga selalu ada Super Administrator. Peran baru ditambahkan sebagai case enum bersama modul yang membutuhkannya.
- **Log Aktivitas** (Pengaturan Sistem, `tb_log_aktivitas`): tambah, ubah, hapus, dan aktifkan kembali data lewat trait `MencatatAktivitas`, serta masuk & keluar lewat listener event `Login`/`Logout` di `AppServiceProvider`, semuanya ditulis `LogAktivitasService` dan hanya bila ada pengguna login. Aksi Ubah menyimpan label isian yang berubah tanpa nilai lama/baru, dan percobaan masuk yang gagal tidak dicatat (pilihan pemilik). Halamannya daftar baca tanpa tambah, ubah, dan hapus per baris; tombol Kosongkan Log menghapus permanen semua log lalu mencatat pengosongan itu sebagai satu baris (pilihan pemilik); Alamat IP hanya di modal Lihat dan tanpa filter jenis data agar kartu filter satu baris (pilihan pemilik). Disimpan 7 hari (`LogAktivitas::MASA_SIMPAN_HARI`): log yang lebih lama dihapus permanen setiap kali log baru ditulis, tanpa cron (pilihan pemilik).
- Produksi: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS, cookie `secure`. Header keamanan belum ada (ROADMAP Fase 5).

## 11. Kualitas (ISO/IEC 25010)

Targetnya mendekati ISO/IEC 25010 dengan praktik yang cukup, bukan selengkap mungkin.

| Karakteristik | Praktik yang cukup |
|---|---|
| Kesesuaian fungsi | Feature test alur CRUD & aturan bisnis; unit test Service yang memuat logika |
| Efisiensi kinerja | Paginasi server-side, eager loading, indeks kolom filter/FK, cache config/route di produksi, ukuran gambar dibatasi |
| Kompatibilitas | Hanya fitur standar MySQL 8/PHP 8.3; modul berinteraksi lewat relasi/Service |
| Kemudahan pakai | Komponen Blade seragam, pesan berbahasa Indonesia yang jelas, konfirmasi aksi, responsif |
| Keandalan | Transaksi DB untuk operasi multi-langkah, soft delete, halaman error yang ramah |
| Keamanan | §10: validasi seluruh input, escape output, berkas internal privat |
| Pemeliharaan | Struktur modular, konvensi ketat, controller tipis, `composer check` sebelum commit, dokumen ringkas & mutakhir |
| Portabilitas | Konfigurasi lewat `.env`, tanpa path absolut, deploy terdokumentasi & dapat diulang |
| Keselamatan | Soft delete, `php artisan down` saat deploy, migrasi produksi hanya maju setelah tahap uji (§2.4) |

## 12. Definition of Done (per fitur)

Butir yang tidak relevan dengan perubahannya dilewati.

- [ ] Migration, model (+ relasi eksplisit), factory, dan seeder sesuai `docs/DATABASE.md`; seeder = data lokal (§2.4)
- [ ] Form Request untuk store & update, pesan berbahasa Indonesia
- [ ] Controller, rute bernama, entri menu `config/menu.php`
- [ ] View memakai layout & komponen, responsif (desktop, tablet, ponsel)
- [ ] Frontend publik: `npm run build`, lalu cek visual halaman yang terdampak di kedua tema (1536, 390 & 360 px)
- [ ] Tampilan dibandingkan dengan acuan sebatas halaman yang terdampak, lalu acuan halaman itu diperbarui (§9 › Acuan Tampilan)
- [ ] Feature test (akses tamu ditolak, validasi, create/read/update/delete) dan unit test untuk Service yang memuat logika
- [ ] `composer check` lulus
- [ ] Catatan deploy (§2.10) dan usulan pesan commit
- [ ] Item terkait di `docs/ROADMAP.md` dicentang
