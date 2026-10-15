# Desain Database — ERP TKAB

Sumber kebenaran untuk seluruh skema. Perubahan skema harus disetujui pemilik proyek dan dicatat di sini **sebelum** migration dibuat.

## Konvensi Umum

- Tabel domain: `tb_<entitas>` (tunggal). PK: `id_<entitas>` BIGINT UNSIGNED AUTO_INCREMENT (`$table->id('id_<entitas>')`).
- FK bernama sama dengan PK rujukan. Bila ada dua FK ke tabel yang sama, beri akhiran: `id_pengguna_pengirim`, `id_pengguna_penerima`.
- Setiap tabel domain memiliki `created_at`, `updated_at`, `deleted_at` (soft delete).
- Boolean berawalan `status_` (mis. `status_baca`, `status_aktif`).
- Nilai pilihan tetap: `VARCHAR(20)` + PHP backed enum (bukan tipe ENUM MySQL, agar mudah ditambah).
- File: `VARCHAR(255)` berisi path relatif terhadap disk penyimpanan.
- Unique pada tabel ber-soft-delete divalidasi di Form Request dengan `->withoutTrashed()`; constraint UNIQUE di database hanya untuk kolom yang tidak boleh berulang sama sekali (mis. slug atau nomor dokumen yang dibuat sistem).
- Relasi polimorfik (bila kelak dipakai): `nullableMorphs('<nama>')` + alias stabil lewat `Relation::enforceMorphMap()` di `AppServiceProvider` (jangan menyimpan nama class penuh).
- Teks panjang yang **tampil di frontend** diisi editor WYSIWYG dan disimpan sebagai HTML tersanitasi (`HtmlSanitizerService`: p, br, strong, em, u, ul, ol, li, a[href], serta `style="text-align: center|right|justify"` pada paragraf). Batas panjangnya dihitung dari teks yang terlihat (`App\Rules\PanjangTeksHtml`), bukan markup; tipe kolom tetap TEXT (maks. 16.000 karakter HTML). Pengecualian: isi artikel (LONGTEXT) boleh memuat sub-judul h2/h3 dan dibatasi 30.000 karakter terlihat / 100.000 karakter HTML. Teks internal yang tidak tampil di frontend (mis. meta deskripsi, alamat identitas) tetap teks polos.
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
| cta | JSON | array objek, mis. `[{"label":"Hubungi Kami","url":"#kontak","gaya":"primary"},{"label":"Lihat Portofolio","url":"#portofolio","gaya":"secondary"}]`; cast `array`. URL berupa anchor seksi beranda (`#…`) atau situs lain; jangan path halaman publik yang belum ada |
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

### tb_portofolio
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_portofolio | BIGINT UNSIGNED PK | |
| judul | VARCHAR(200) | tidak wajib unik |
| slug | VARCHAR(220) UNIQUE | URL detail di frontend, dibuat otomatis dari judul oleh `PortofolioService` |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | |
| kategori | VARCHAR(50) | INDEX. Teks bebas (mis. Website, Mobile Apps); form memberi saran kategori yang sudah ada (`<datalist>`) agar penulisannya seragam. Dinormalisasi ke tabel sendiri bila dibutuhkan |
| created_at, updated_at, deleted_at | | |

Slug ikut berubah saat judul diubah; penguncian seperti artikel ditinjau saat halaman detail portofolio dibuat.

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

### tb_faq
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_faq | BIGINT UNSIGNED PK | |
| pertanyaan | VARCHAR(255) | |
| jawaban | TEXT | HTML tersanitasi dari editor WYSIWYG |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX |
| created_at, updated_at, deleted_at | | |

### tb_kontak_kami
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kontak_kami | BIGINT UNSIGNED PK | |
| nama | VARCHAR(100) | |
| email | VARCHAR(150) | |
| subjek | VARCHAR(200) | |
| pesan | TEXT | |
| tanggal | DATETIME | |
| status_baca | BOOLEAN DEFAULT false | INDEX |
| created_at, updated_at, deleted_at | | |

Data masuk dari formulir kontak publik. Admin hanya: daftar (filter sudah/belum dibaca), lihat (otomatis tandai dibaca), tandai belum dibaca, hapus.

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

---

## C. Modul Masa Depan (gambaran, belum dibuat)

Untuk memastikan desain hari ini kompatibel:
- `tb_peserta` — data orang (mahasiswa/peserta) yang dipakai bersama oleh pelatihan, sertifikasi, dan bootcamp.
- `tb_pelatihan`, `tb_sertifikasi`, `tb_bootcamp` — master program/batch.
- `tb_pendaftaran_*` — relasi peserta ↔ program, status, nilai, sertifikat.
- `tb_klien`, `tb_proyek`, `tb_invoice` — untuk jasa website & mobile apps.
