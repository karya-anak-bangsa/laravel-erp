# Desain Database — ERP TKAB

Sumber kebenaran untuk seluruh skema. Perubahan skema harus disetujui pemilik proyek dan dicatat di sini **sebelum** migration dibuat.

## Konvensi Umum

- Tabel domain: `tb_<entitas>` (tunggal). PK: `id_<entitas>` BIGINT UNSIGNED AUTO_INCREMENT (`$table->id('id_<entitas>')`).
- FK bernama sama dengan PK rujukan. Bila ada dua FK ke tabel yang sama, beri akhiran: `id_pengguna_pengirim`, `id_pengguna_penerima`.
- Setiap tabel domain memiliki `created_at`, `updated_at`, `deleted_at` (soft delete).
- Boolean berawalan `status_` (mis. `status_baca`, `status_aktif`).
- Nilai pilihan tetap: `VARCHAR(20)` + PHP backed enum (bukan tipe ENUM MySQL, agar mudah ditambah).
- File: `VARCHAR(255)` berisi path relatif terhadap disk penyimpanan.
- Unique pada tabel ber-soft-delete divalidasi di Form Request dengan `->withoutTrashed()`; constraint UNIQUE di database hanya untuk kolom yang tidak boleh berulang sama sekali (mis. nomor dokumen yang dibuat sistem).
- Relasi polimorfik (bila kelak dipakai): `nullableMorphs('<nama>')` + alias stabil lewat `Relation::enforceMorphMap()` di `AppServiceProvider` (jangan menyimpan nama class penuh).
- Teks panjang yang **tampil di frontend** diisi editor WYSIWYG dan disimpan sebagai HTML tersanitasi (`HtmlSanitizerService`: p, br, strong, em, u, ul, ol, li, a[href], serta `style="text-align: center|right|justify"` pada paragraf). Batas panjangnya dihitung dari teks yang terlihat (`App\Rules\PanjangTeksHtml`), bukan markup; tipe kolom tetap TEXT (maks. 16.000 karakter HTML). Pengecualian: isi artikel (LONGTEXT) boleh memuat sub-judul h2/h3 dan dibatasi 30.000 karakter terlihat / 100.000 karakter HTML. Teks internal yang tidak tampil di frontend (mis. meta deskripsi, alamat identitas) tetap teks polos.
- Kolom bertanda **(+)** adalah tambahan di luar rancangan awal yang **sudah disetujui** pemilik proyek.

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
- Model `App\Models\Pengguna extends Authenticatable`. Set `AUTH_MODEL` / `config/auth.php` ke model ini.
- Hapus `App\Models\User` dan definisi tabel `users` dari migration bawaan; **pertahankan** `password_reset_tokens` dan `sessions` dari migration tersebut.
- **Tidak ada kolom `remember_token`** (keputusan pemilik proyek). Konsekuensinya:
  - Model `Pengguna` wajib berisi `protected $rememberTokenName = '';` agar Laravel tidak pernah membaca/menulis kolom tersebut (termasuk saat logout dan reset password).
  - Tidak ada fitur "ingat saya" di halaman login; panggil `Auth::attempt($kredensial)` tanpa argumen kedua.

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
| meta_keyword | VARCHAR(255) NULL | |
| logo_website | VARCHAR(255) | path file |
| favicon_website | VARCHAR(255) | path file |
| email | VARCHAR(150) | |
| telepon | VARCHAR(30) | |
| alamat | TEXT | |
| link_youtube | VARCHAR(255) NULL | |
| link_instagram | VARCHAR(255) NULL | dikoreksi dari `link_instragram` |
| link_whatsapp | VARCHAR(255) NULL | |
| created_at, updated_at, deleted_at | | |

### tb_hero
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_hero | BIGINT UNSIGNED PK | |
| judul | VARCHAR(200) | |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG (lihat Konvensi Umum) |
| gambar | VARCHAR(255) | |
| keyword | JSON | array string, mis. `["Website","Mobile Apps","Pelatihan IT","Sertifikasi IT","Bootcamp"]`; cast `array` |
| cta | JSON | array objek, mis. `[{"label":"Hubungi Kami","url":"#kontak","gaya":"primary"},{"label":"Lihat Portofolio","url":"#portofolio","gaya":"secondary"}]`; cast `array`. URL berupa anchor seksi beranda (`#…`) atau situs lain; jangan path halaman publik yang belum ada |
| status_aktif | BOOLEAN DEFAULT true | **(+)** INDEX. Menentukan hero yang tampil di frontend. Boleh banyak hero, tetapi **hanya satu yang aktif** — dijaga `HeroService` (mengaktifkan satu hero menonaktifkan yang lain) |
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
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) UNIQUE | **(+)** URL detail di frontend. Dibuat otomatis dari judul oleh `PortofolioService` |
| deskripsi | TEXT | HTML tersanitasi dari editor WYSIWYG |
| gambar | VARCHAR(255) | |
| kategori | VARCHAR(50) | INDEX. Sementara teks bebas (mis. Website, Mobile Apps). Dinormalisasi ke tabel sendiri bila dibutuhkan |
| created_at, updated_at, deleted_at | | |

Catatan implementasi: slug dibuat sistem sehingga memakai constraint UNIQUE di database (termasuk baris terhapus, agar tidak bentrok bila kelak dipulihkan). Slug yang sudah dipakai diberi akhiran angka (`-2`, `-3`, …); dasar slug dibatasi 200 karakter. Slug ikut berubah saat judul diubah — ditinjau ulang di Fase 4 begitu halaman detail tayang. Judul tidak wajib unik. Form memberi saran kategori yang sudah ada (`<datalist>`) agar penulisannya seragam.

### tb_kategori_artikel
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kategori_artikel | BIGINT UNSIGNED PK | |
| nama_kategori | VARCHAR(100) | |
| created_at, updated_at, deleted_at | | |

### tb_artikel
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_artikel | BIGINT UNSIGNED PK | |
| id_kategori_artikel | BIGINT UNSIGNED FK → tb_kategori_artikel | `restrictOnDelete` |
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) | **(+)** URL artikel, dibuat otomatis dari judul |
| deskripsi | LONGTEXT | isi artikel (HTML tersanitasi) |
| gambar | VARCHAR(255) | |
| tanggal | DATE | INDEX |
| status_publikasi | VARCHAR(20) DEFAULT 'draf' | **(+)** enum `StatusPublikasi`: draf, terbit |
| created_at, updated_at, deleted_at | | |

Catatan implementasi: slug dibuat `SlugService` (dipakai bersama portofolio) dengan aturan sama seperti portofolio — UNIQUE di database, akhiran angka, ikut berubah saat judul diubah. `deskripsi` = isi artikel dari editor WYSIWYG dengan sub-judul H2/H3. Hanya artikel `terbit` yang tampil di frontend. Kategori yang masih dipakai artikel (yang belum dihapus) tidak bisa dihapus; dicek di aplikasi karena `restrictOnDelete` tidak berlaku untuk soft delete.

### tb_faq
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_faq | BIGINT UNSIGNED PK | |
| pertanyaan | VARCHAR(255) | |
| jawaban | TEXT | HTML tersanitasi dari editor WYSIWYG |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX. Diseragamkan dengan `tb_layanan` (sebelumnya `urutan`) |
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
| status_baca | BOOLEAN DEFAULT false | INDEX. Dikoreksi dari `status-baca` |
| created_at, updated_at, deleted_at | | |

Admin hanya: daftar (filter sudah/belum dibaca), lihat (otomatis tandai dibaca), tandai belum dibaca, hapus. Data masuk dari form publik (Fase 4).

### tb_template **(+)**
Tabel baru yang disetujui pemilik 2026-10-09; dibuat di Fase 4 langkah 3. Berisi template tampilan frontend publik, dikelola lewat menu Pengaturan Sistem › Manajemen Template. Boleh banyak template, tetapi **hanya satu yang aktif per jenis** (satu Full Color, satu Monochrome). Aturan ini dijaga `TemplateService` dengan pola yang sama seperti Hero: mengaktifkan satu template otomatis menonaktifkan template lain yang sejenis.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_template | BIGINT UNSIGNED PK | |
| nama | VARCHAR(100) | mis. "TKAB Full Color". Unik di antara template yang belum dihapus (Form Request, `withoutTrashed`) |
| jenis | VARCHAR(20) | enum `JenisTemplate`: full_color, monochrome |
| warna_utama | CHAR(7) NULL | hex `#rrggbb`, wajib bila full_color: judul, band ajakan, footer |
| warna_aksen | CHAR(7) NULL | hex `#rrggbb`, wajib bila full_color: tombol utama, label seksi, tautan |
| nada_dasar | VARCHAR(20) NULL | enum `NadaDasar`: netral, zinc, stone, slate (skala abu-abu); wajib bila monochrome |
| font | VARCHAR(50) | enum `FontTemplate`: plus_jakarta_sans, geist, inter, poppins, manrope, dm_sans |
| sudut | VARCHAR(20) | enum `SudutTemplate`: tajam, sedang, bulat |
| skala | VARCHAR(20) | enum `SkalaTemplate`: ringkas, standar, lega. Ukuran desktop (≥1024px) tinggi navbar, logo, teks menu, tombol besar, dan judul seksi; nilai per jenis di enum, naik satu tingkat otomatis di layar ≥1920px. Jarak antarseksi menyusul saat review seksi (disetujui pemilik 2026-10-11) |
| lebar_konten | VARCHAR(20) | enum `LebarKonten`: standar (1280px), lebar (1440px). Lebar maksimum isi halaman di monitor ≥1920px; di bawahnya selalu 1280px (disetujui pemilik 2026-10-11) |
| ketebalan_judul | VARCHAR(20) | enum `KetebalanJudul`: semibold, bold, extrabold — judul hero & judul seksi (disetujui pemilik 2026-10-11) |
| bayangan | VARCHAR(20) | enum `BayanganTemplate`: tanpa, halus, tegas — bayangan tombol, kartu, dan gambar hero (disetujui pemilik 2026-10-11) |
| status_aktif | BOOLEAN DEFAULT false | |
| created_at, updated_at, deleted_at | | |

Indeks: `(jenis, status_aktif)`.

Catatan implementasi:
- Template hanya berisi pengaturan tampilan. Susunan halaman tiap jenis dibuat developer di kode. Admin tidak mengunggah berkas atau kode template, karena rawan keamanan dan server tidak bisa membangun aset.
- Kolom yang tidak relevan dengan jenisnya disimpan NULL: `warna_*` untuk monochrome, `nada_dasar` untuk full_color.
- Turunan keterbacaan tidak disimpan, tetapi dihitung saat render: warna teks yang dijamin kontras ≥ 4,5:1 di atas putih, dan warna teks di atas tombol aksen (putih atau gelap).
- Template aktif tidak bisa dihapus, dinonaktifkan langsung, atau diubah jenisnya, agar frontend selalu punya tepat satu template per jenis.
- Data awal: dua template aktif, "TKAB Full Color" (#15253F, #CB1839, Plus Jakarta Sans, sudut sedang, skala ringkas, konten lebar, judul extrabold, bayangan halus — pilihan pemilik) dan "TKAB Monochrome" (netral, Geist, sudut sedang, skala ringkas, konten lebar, judul semibold, bayangan halus).
- Pengaturan ukuran & gaya berupa pilihan bertingkat, bukan angka piksel bebas, agar tampilan tidak bisa rusak dan satu pilihan sekaligus menyesuaikan semua ukuran layar (keputusan pemilik 2026-10-11). `skala` dan `lebar_konten` sudah berlaku lewat `config/tema.php` → `TemaService`; `ketebalan_judul` dan `bayangan` dibuat bersama tabel ini.

---

## C. Modul Masa Depan (gambaran, belum dibuat)

Untuk memastikan desain hari ini kompatibel:
- `tb_peserta` — data orang (mahasiswa/peserta) yang dipakai bersama oleh pelatihan, sertifikasi, dan bootcamp.
- `tb_pelatihan`, `tb_sertifikasi`, `tb_bootcamp` — master program/batch.
- `tb_pendaftaran_*` — relasi peserta ↔ program, status, nilai, sertifikat.
- `tb_klien`, `tb_proyek`, `tb_invoice` — untuk jasa website & mobile apps.
