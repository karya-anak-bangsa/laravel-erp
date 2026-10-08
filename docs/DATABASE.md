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
| link_gmap | TEXT NULL | URL embed Google Maps (bisa panjang) |
| link_youtube | VARCHAR(255) NULL | |
| link_instagram | VARCHAR(255) NULL | dikoreksi dari `link_instragram` |
| link_whatsapp | VARCHAR(255) NULL | |
| created_at, updated_at, deleted_at | | |

### tb_hero
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_hero | BIGINT UNSIGNED PK | |
| judul | VARCHAR(200) | |
| deskripsi | TEXT | |
| gambar | VARCHAR(255) | |
| keyword | JSON | array string, mis. `["Website","Mobile Apps","Pelatihan IT","Sertifikasi IT","Bootcamp"]`; cast `array` |
| cta | JSON | array objek, mis. `[{"label":"Hubungi Kami","url":"#kontak","gaya":"primary"},{"label":"Lihat Portofolio","url":"/portofolio","gaya":"secondary"}]`; cast `array` |
| status_aktif | BOOLEAN DEFAULT true | **(+)** INDEX. Menentukan hero yang tampil di frontend. Boleh banyak hero, tetapi **hanya satu yang aktif** — dijaga `HeroService` (mengaktifkan satu hero menonaktifkan yang lain) |
| created_at, updated_at, deleted_at | | |

Validasi: `keyword` array max 10, `keyword.*` string max 50; `cta` array max 3, `cta.*.label` wajib max 30, `cta.*.url` wajib max 255, `cta.*.gaya` in:primary,secondary. Form memakai input repeater (tambah/hapus baris).

### tb_layanan
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_layanan | BIGINT UNSIGNED PK | |
| judul | VARCHAR(150) | |
| deskripsi | TEXT | |
| gambar | VARCHAR(255) | |
| keterangan | TEXT NULL | |
| urutan_ke | UNSIGNED SMALLINT DEFAULT 0 | INDEX |
| created_at, updated_at, deleted_at | | |

### tb_portofolio
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_portofolio | BIGINT UNSIGNED PK | |
| judul | VARCHAR(200) | |
| slug | VARCHAR(220) | **(+)** URL detail di frontend |
| deskripsi | TEXT | |
| gambar | VARCHAR(255) | |
| kategori | VARCHAR(50) | INDEX. Sementara teks bebas (mis. Website, Mobile Apps). Dinormalisasi ke tabel sendiri bila dibutuhkan |
| created_at, updated_at, deleted_at | | |

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

### tb_faq
| Kolom | Tipe | Keterangan |
|---|---|---|
| id_faq | BIGINT UNSIGNED PK | |
| pertanyaan | VARCHAR(255) | |
| jawaban | TEXT | |
| urutan | UNSIGNED SMALLINT DEFAULT 0 | INDEX |
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

---

## C. Modul Masa Depan (gambaran, belum dibuat)

Untuk memastikan desain hari ini kompatibel:
- `tb_peserta` — data orang (mahasiswa/peserta) yang dipakai bersama oleh pelatihan, sertifikasi, dan bootcamp.
- `tb_pelatihan`, `tb_sertifikasi`, `tb_bootcamp` — master program/batch.
- `tb_pendaftaran_*` — relasi peserta ↔ program, status, nilai, sertifikat.
- `tb_klien`, `tb_proyek`, `tb_invoice` — untuk jasa website & mobile apps.
