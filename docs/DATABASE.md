# Desain Database — ERP TKAB

Sumber kebenaran untuk seluruh skema. Perubahan skema harus disetujui pemilik proyek dan dicatat di sini **sebelum** migration dibuat.

## Konvensi Umum

- Tabel domain: `tb_<entitas>` (tunggal). PK: `id_<entitas>` BIGINT UNSIGNED AUTO_INCREMENT (`$table->id('id_<entitas>')`).
- FK bernama sama dengan PK rujukan. Bila ada dua FK ke tabel yang sama, beri akhiran: `id_pengguna_pengirim`, `id_pengguna_penerima`.
- Setiap tabel domain memiliki `created_at`, `updated_at`, `deleted_at` (soft delete), kecuali tabel gambar anak yang barisnya bagian dari induknya (`tb_gambar_portofolio`).
- Boolean berawalan `status_` (mis. `status_baca`, `status_aktif`).
- Nilai pilihan tetap: `VARCHAR(20)` + PHP backed enum (bukan tipe ENUM MySQL, agar mudah ditambah).
- File: `VARCHAR(255)` berisi path relatif terhadap disk penyimpanan.
- Unique pada tabel ber-soft-delete divalidasi di Form Request dengan `->withoutTrashed()`; constraint UNIQUE di database hanya untuk kolom yang tidak boleh berulang sama sekali (mis. slug atau nomor dokumen yang dibuat sistem).
- Relasi polimorfik (bila kelak dipakai): `nullableMorphs('<nama>')` + alias stabil lewat `Relation::enforceMorphMap()` di `AppServiceProvider` (jangan menyimpan nama class penuh).
- Teks panjang yang **tampil di frontend** diisi editor WYSIWYG dan disimpan sebagai HTML tersanitasi (`HtmlSanitizerService`: p, br, strong, em, u, ul, ol, li, a[href], serta `style="text-align: center|right|justify"` pada paragraf). Batas panjangnya dihitung dari teks yang terlihat (`App\Rules\PanjangTeksHtml`), bukan markup; tipe kolom tetap TEXT (maks. 16.000 karakter HTML). Pengecualian: isi artikel dan isi materi tutorial (LONGTEXT) boleh memuat sub-judul h2/h3 dan dibatasi 30.000 karakter terlihat / 100.000 karakter HTML; isi materi juga boleh memuat kode (`code`, `pre`). Teks internal yang tidak tampil di frontend (mis. meta deskripsi, alamat identitas) tetap teks polos.
- Slug dibuat sistem lewat `SlugService`: UNIQUE di database (termasuk baris terhapus, agar tidak bentrok bila kelak dipulihkan), akhiran angka (`-2`, `-3`, …) bila sudah dipakai, dasar slug maks. 200 karakter.

---

## A. Autentikasi

### tb_pengguna
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_pengguna | BIGINT UNSIGNED PK | |
| nama | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | cast `hashed` |
| created_at, updated_at, deleted_at | TIMESTAMP NULL | |

Catatan implementasi:
- Model `App\Models\Pengguna extends Authenticatable` (dipakai `config/auth.php`). Tabel bawaan `password_reset_tokens` dan `sessions` tetap dipakai.
- **Tidak ada kolom `remember_token`**: model berisi `protected $rememberTokenName = '';` agar Laravel tidak pernah membaca/menulis kolom itu (termasuk saat logout dan reset password), dan login memanggil `Auth::attempt($kredensial)` tanpa argumen kedua.

---

## B. Company Profile

### tb_identitas (singleton — selalu 1 baris, dibuat oleh seeder)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_identitas | BIGINT UNSIGNED PK | |
| nama_perusahaan | VARCHAR(150) | |
| judul_website | VARCHAR(150) | |
| alamat_website | VARCHAR(255) | URL |
| meta_deskripsi | VARCHAR(255) NULL | |
| meta_keyword | VARCHAR(255) NULL | tidak dirender di halaman |
| logo_website | VARCHAR(255) | path file |
| favicon_website | VARCHAR(255) | path file (PNG/ICO) |
| email | VARCHAR(150) | |
| telepon | VARCHAR(30) | |
| alamat | TEXT | |
| link_youtube | VARCHAR(255) NULL | |
| link_instagram | VARCHAR(255) NULL | |
| link_whatsapp | VARCHAR(255) NULL | |
| created_at, updated_at, deleted_at | | |

### tb_hero
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_hero | BIGINT UNSIGNED PK | |
| judul | VARCHAR(200) | |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | |
| keyword | JSON | array string, mis. `["Website","Mobile Apps","Pelatihan IT","Sertifikasi IT","Bootcamp"]`; cast `array` |
| cta | JSON | array objek, mis. `[{"label":"Hubungi Kami","url":"https://wa.me/6281234567890","gaya":"primary"},{"label":"Lihat Portofolio","url":"#portofolio","gaya":"secondary"}]`; cast `array`. URL berupa anchor seksi beranda (`#…`) atau situs lain; jangan path halaman publik yang belum ada |
| status_aktif | BOOLEAN DEFAULT true | INDEX. Menentukan hero yang tampil di frontend. Boleh banyak hero, tetapi **hanya satu yang aktif** — dijaga `HeroService` (mengaktifkan satu hero menonaktifkan yang lain) |
| created_at, updated_at, deleted_at | | |

Validasi: `keyword` array max 10, `keyword.*` string max 50; `cta` array max 3, `cta.*.label` wajib max 30, `cta.*.url` wajib max 255, `cta.*.gaya` in:primary,secondary. Form memakai input repeater (tambah/hapus baris).

### tb_layanan
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_layanan | BIGINT UNSIGNED PK | |
| judul | VARCHAR(150) | |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | |
| keterangan | TEXT NULL | HTML tersanitasi dari editor WYSIWYG |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX |
| created_at, updated_at, deleted_at | | |

### tb_kategori_portofolio
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kategori_portofolio | BIGINT UNSIGNED PK | |
| nama_kategori | VARCHAR(100) | |
| created_at, updated_at, deleted_at | | |

Catatan implementasi:
- Tanpa slug karena tidak ada halaman kategori portofolio di frontend (tab filter kategori ditolak pemilik 2026-10-11).
- Kartu portofolio di beranda diurutkan menurut urutan kategori dibuat (`id_kategori_portofolio`), sehingga urutannya tetap walau nama diubah. Lima kategori awal dari `KategoriPortofolioSeeder` mengikuti urutan layanan: Website, Mobile Apps, Pelatihan IT, Sertifikasi IT, Bootcamp. Di admin, kategori tetap diurutkan abjad seperti data master lain.
- Kategori yang masih dipakai portofolio (yang belum dihapus) tidak bisa dihapus; dicek di aplikasi karena `restrictOnDelete` tidak berlaku untuk soft delete.

### tb_portofolio
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_portofolio | BIGINT UNSIGNED PK | |
| id_kategori_portofolio | BIGINT UNSIGNED FK → tb_kategori_portofolio | `restrictOnDelete` |
| judul | VARCHAR(200) | tidak wajib unik |
| slug | VARCHAR(220) UNIQUE | URL detail di frontend, dibuat otomatis dari judul oleh `PortofolioService` |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | gambar utama: kartu, kolom samping, gambar saat dibagikan, dan slide pertama carousel detail |
| created_at, updated_at, deleted_at | | |

Slug dibuat sekali saat portofolio disimpan dan tidak ikut berubah saat judul diubah: portofolio tidak punya draf, jadi langsung tayang di `/portofolio/{slug}` dan tautannya bisa langsung tersebar.

### tb_gambar_portofolio
Gambar tambahan portofolio, tampil di carousel halaman detail setelah gambar utama (`tb_portofolio.gambar`).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_gambar_portofolio | BIGINT UNSIGNED PK | |
| id_portofolio | BIGINT UNSIGNED FK → tb_portofolio | `cascadeOnDelete` (hanya berlaku bila portofolio dihapus permanen) |
| gambar | VARCHAR(255) | |
| created_at, updated_at | | |

Catatan implementasi:
- Setiap portofolio paling banyak 5 gambar termasuk gambar utama, jadi paling banyak 4 baris di sini (pilihan pemilik 2026-10-18). Urutan tampil = urutan unggah (`id_gambar_portofolio`), tanpa kolom urutan.
- Tanpa `deleted_at`: barisnya bagian dari portofolio seperti kolom `gambar`, jadi gambar yang dibuang lewat form dihapus permanen beserta berkasnya, sama seperti saat gambar utama diganti. Soft delete portofolio tidak menyentuh gambarnya.

### tb_kategori_artikel
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kategori_artikel | BIGINT UNSIGNED PK | |
| nama_kategori | VARCHAR(100) | |
| slug | VARCHAR(220) UNIQUE | URL halaman kategori (`/artikel/kategori/{slug}`), dibuat otomatis dari nama oleh `KategoriArtikelService` |
| created_at, updated_at, deleted_at | | |

Slug ikut berubah saat nama diubah **hanya selama kategori belum punya artikel terbit**; setelah itu dikunci agar tautan yang sudah tersebar tidak rusak (tanpa kolom tambahan).

### tb_artikel
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_artikel | BIGINT UNSIGNED PK | |
| id_kategori_artikel | BIGINT UNSIGNED FK → tb_kategori_artikel | `restrictOnDelete` |
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) | URL artikel, dibuat otomatis dari judul |
| deskripsi | LONGTEXT | isi artikel (HTML tersanitasi, dengan sub-judul H2/H3) |
| gambar | VARCHAR(255) | |
| tanggal | DATE | INDEX |
| status_publikasi | VARCHAR(20) DEFAULT 'draf' | enum `StatusPublikasi`: draf, terbit |
| created_at, updated_at, deleted_at | | |

Catatan implementasi: slug hanya ikut berubah saat judul diubah selama artikel masih **draf**; artikel yang sudah terbit slugnya dikunci (juga bila dikembalikan ke draf pada perubahan yang sama) agar tautan yang sudah dibagikan tidak rusak. Hanya artikel `terbit` yang tampil di frontend. Kategori yang masih dipakai artikel (yang belum dihapus) tidak bisa dihapus; dicek di aplikasi karena `restrictOnDelete` tidak berlaku untuk soft delete.

### tb_kategori_tutorial
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kategori_tutorial | BIGINT UNSIGNED PK | |
| nama_kategori | VARCHAR(100) | topik spesifik, mis. Laravel |
| slug | VARCHAR(220) UNIQUE | URL halaman kategori (`/tutorial/kategori/{slug}`), dibuat otomatis dari nama oleh `KategoriTutorialService` |
| created_at, updated_at, deleted_at | | |

Catatan implementasi:
- Slug ikut berubah saat nama diubah **hanya selama kategori belum punya tutorial tayang** (terbit dan bermateri); setelah itu dikunci, sama dengan slug kategori artikel. Slug tutorial `kategori` dicadangkan `TutorialService` agar alamat materinya tidak tertutup halaman kategori.
- Lima kategori awal dari `KategoriTutorialSeeder`: Deep Learning, JavaScript, Laravel, Machine Learning, TypeScript. Berbeda dengan kategori portofolio yang umum (jenis layanan), kategori tutorial (dan artikel) berupa topik spesifik. Di admin diurutkan abjad seperti data master lain.
- Kategori yang masih dipakai tutorial (yang belum dihapus) tidak bisa dihapus; dicek di aplikasi karena `restrictOnDelete` tidak berlaku untuk soft delete.

### tb_tutorial
Seri materi berurutan, mis. "Dasar-Dasar Laravel 13": materi sederhana untuk promosi (pemasaran) di website. Berbeda dari materi Pelatihan IT, Sertifikasi IT, dan Bootcamp yang lebih kompleks dan mengikuti standar SKKNI; modul-modul itu (bagian C) kelak berdiri sendiri tanpa mengubah tabel ini.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_tutorial | BIGINT UNSIGNED PK | |
| id_kategori_tutorial | BIGINT UNSIGNED FK → tb_kategori_tutorial | `restrictOnDelete` |
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) UNIQUE | URL halaman tutorial, dibuat otomatis dari judul oleh `TutorialService` |
| deskripsi | TEXT | pengantar seri (yang dipelajari, prasyarat), HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | sampul: kartu, halaman tutorial, dan gambar saat tutorial maupun materinya dibagikan |
| status_publikasi | VARCHAR(20) DEFAULT 'draf' | enum `StatusPublikasi`: draf, terbit |
| created_at, updated_at, deleted_at | | |

Catatan implementasi:
- Status hanya di tingkat tutorial: tutorial tampil di frontend bila `terbit` **dan** punya minimal satu materi, lalu semua materinya ikut tampil.
- Slug mengikuti aturan slug artikel: ikut berubah saat judul diubah selama tutorial masih draf, dikunci setelah terbit.
- Menghapus tutorial = soft delete tutorial beserta semua materinya dalam satu transaksi (`TutorialService::hapus()`).

### tb_materi_tutorial
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_materi_tutorial | BIGINT UNSIGNED PK | |
| id_tutorial | BIGINT UNSIGNED FK → tb_tutorial | `restrictOnDelete`. Tidak bisa diganti setelah materi dibuat karena alamat materi memuat slug tutorialnya |
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) | UNIQUE `(id_tutorial, slug)`: unik per tutorial, dibuat otomatis dari judul oleh `MateriTutorialService` |
| deskripsi | LONGTEXT | isi materi: HTML tersanitasi dengan sub-judul h2/h3 serta kode sebaris (`code`) dan blok kode (`pre`); batasnya sama dengan isi artikel |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX `(id_tutorial, urutan_ke)`. Kosong saat disimpan = setelah materi terakhir tutorialnya |
| created_at, updated_at, deleted_at | | |

Catatan implementasi:
- Materi diurutkan `urutan_ke` lalu `id_materi_tutorial`; nomor tampil ("Materi 2 dari 8") dihitung dari posisi, jadi angka urutan boleh berselang atau kembar.
- Tanpa gambar sendiri: halaman materi memakai sampul tutorialnya.
- Slug ikut berubah saat judul diubah selama tutorialnya belum terbit; setelah itu dikunci.

### tb_faq
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_faq | BIGINT UNSIGNED PK | |
| pertanyaan | VARCHAR(255) | |
| jawaban | TEXT | HTML tersanitasi dari editor WYSIWYG |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX |
| created_at, updated_at, deleted_at | | |

### tb_template
Template tampilan frontend publik, dikelola lewat Pengaturan Sistem › Manajemen Template. Boleh banyak template, tetapi **hanya satu yang aktif per jenis** (satu Full Color, satu Monochrome), dijaga `TemplateService` dengan pola yang sama seperti Hero.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_template | BIGINT UNSIGNED PK | |
| nama | VARCHAR(100) | mis. "TKAB Full Color". Unik di antara template yang belum dihapus (Form Request, `withoutTrashed`) |
| jenis | VARCHAR(20) | enum `JenisTemplate`: full_color, monochrome |
| warna_utama | CHAR(7) NULL | hex `#rrggbb`, wajib bila full_color: judul, band ajakan, footer |
| warna_aksen | CHAR(7) NULL | hex `#rrggbb`, wajib bila full_color: tombol utama, label seksi, tautan |
| nada_dasar | VARCHAR(20) NULL | enum `NadaDasar`: netral, biru, hijau, ungu, cokelat (skala abu bertinta: kecerahan Tailwind neutral, kekuatan tinta Tailwind slate); wajib bila monochrome |
| font | VARCHAR(50) | enum `FontTemplate`: plus_jakarta_sans, geist, inter, poppins, manrope, dm_sans |
| sudut | VARCHAR(20) | enum `SudutTemplate`: tajam, sedang, bulat |
| skala | VARCHAR(20) | enum `SkalaTemplate`: ringkas, standar, lega. Ukuran desktop (≥1024px) tinggi navbar, logo, teks menu, tombol besar, dan judul seksi; satu tabel nilai untuk kedua jenis, naik satu tingkat otomatis di layar ≥1920px |
| lebar_konten | VARCHAR(20) | enum `LebarKonten`: standar (1280px), lebar (1440px). Lebar maksimum isi halaman di monitor ≥1920px; di bawahnya selalu 1280px |
| ketebalan_judul | VARCHAR(20) | enum `KetebalanJudul`: semibold, bold, extrabold — judul hero, judul seksi, band ajakan, dan halaman error |
| bayangan | VARCHAR(20) | enum `BayanganTemplate`: tanpa, halus, tegas — bayangan permukaan (tombol, isian, kartu, bingkai gambar) |
| status_aktif | BOOLEAN DEFAULT false | |
| created_at, updated_at, deleted_at | | |

Indeks: `(jenis, status_aktif)`.

Catatan implementasi:
- Template hanya berisi pengaturan tampilan berupa pilihan bertingkat, bukan angka piksel bebas, agar tampilan tidak bisa rusak. Susunan halaman tiap jenis dibuat developer di kode; admin tidak mengunggah berkas atau kode template (rawan keamanan, dan server tidak bisa membangun aset).
- Kolom yang tidak relevan dengan jenisnya disimpan NULL: `warna_*` untuk monochrome, `nada_dasar` untuk full_color.
- Turunan keterbacaan tidak disimpan, tetapi dihitung saat render: warna teks dengan kontras ≥ 4,5:1 di atas putih, dan warna teks di atas tombol aksen (putih atau gelap).
- Template aktif tidak bisa dihapus, dinonaktifkan langsung, atau diubah jenisnya, agar frontend selalu punya tepat satu template per jenis.
- Pengaturan Default/Custom di form tidak disimpan sebagai kolom (CLAUDE.md §9 › Frontend Publik).
- Data awal (`TemplateSeeder`, nilainya = `config/tema.php`): "TKAB Full Color" (#15253F, #CB1839, Geist, sudut sedang, skala standar, konten standar, judul semibold, bayangan halus) dan "TKAB Monochrome" (cokelat, Geist, sudut sedang, skala standar, konten standar, judul semibold, bayangan tanpa).

### tb_modul_website
Tampil atau tidaknya modul di website publik, dikelola lewat Pengaturan Sistem › Manajemen Modul. Satu baris per modul; admin hanya mengubah status (tanpa tambah/hapus).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_modul_website | BIGINT UNSIGNED PK | |
| jenis | VARCHAR(20) UNIQUE | enum `JenisModul`: layanan, portofolio, tutorial, artikel, faq, ajakan. Nama dan tempat tampil setiap modul ada di enum |
| status_aktif | BOOLEAN DEFAULT true | Nonaktif = disembunyikan dari menu navbar, Tautan Cepat footer, seksi beranda, kolom samping, dan band ajakan; halaman modulnya 404 dan keluar dari sitemap |
| created_at, updated_at, deleted_at | | |

Catatan implementasi:
- Hero tidak termasuk karena memuat judul utama beranda (h1); hero yang tampil diatur lewat `tb_hero.status_aktif`.
- Modul yang belum punya baris, atau status yang tidak terbaca karena database gagal, dianggap aktif (`ModulWebsiteService`).
- Data awal (`ModulWebsiteSeeder`): enam modul, semuanya aktif; dijalankan ulang hanya menambah modul yang belum punya baris.

---

## C. Modul Masa Depan (gambaran, belum dibuat)

Untuk memastikan desain hari ini kompatibel:
- `tb_peserta` — data orang (mahasiswa/peserta) yang dipakai bersama oleh pelatihan, sertifikasi, dan bootcamp.
- `tb_pelatihan`, `tb_sertifikasi`, `tb_bootcamp` — master program/batch.
- `tb_pendaftaran_*` — relasi peserta ↔ program, status, nilai, sertifikat.
- `tb_klien`, `tb_proyek`, `tb_invoice` — untuk jasa website & mobile apps.
