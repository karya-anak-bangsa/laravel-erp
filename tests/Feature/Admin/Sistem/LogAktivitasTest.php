<?php

use App\Enums\Sistem\AksiAktivitas;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\GambarPortofolio;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Concerns\MencatatAktivitas;
use App\Models\Pengguna;
use App\Models\Sistem\LogAktivitas;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create(['nama' => 'Budi Santoso', 'email' => 'budi@example.com']);
});

/**
 * Ringkasan log urut kejadian: [aksi, alias jenis data, nama data, isian diubah].
 *
 * @return list<array{0: AksiAktivitas, 1: string|null, 2: string|null, 3: list<string>|null}>
 */
function ringkasanLog(): array
{
    return LogAktivitas::query()->oldest('id_log_aktivitas')->get()
        ->map(fn (LogAktivitas $log): array => [$log->aksi, $log->subjek_type, $log->label_subjek, $log->perubahan])
        ->all();
}

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.log-aktivitas.index'))->assertRedirect(route('login'));
});

it('hanya menyediakan daftar baca tanpa tambah, ubah, atau hapus log', function () {
    expect(Route::has('admin.log-aktivitas.index'))->toBeTrue();

    foreach (['create', 'store', 'show', 'edit', 'update', 'destroy'] as $aksi) {
        expect(Route::has("admin.log-aktivitas.{$aksi}"))->toBeFalse();
    }
});

it('menampilkan log terbaru lebih dulu sesuai standar admin', function () {
    $artikel = Artikel::factory()->create();
    LogAktivitas::factory()->for($this->admin, 'pengguna')->create([
        'alamat_ip' => '203.0.113.7',
        'created_at' => '2026-10-21 08:15:00',
    ]);
    LogAktivitas::factory()->for($this->admin, 'pengguna')->create([
        'aksi' => AksiAktivitas::Ubah,
        'subjek_type' => 'artikel',
        'subjek_id' => $artikel->id_artikel,
        'label_subjek' => 'Mengenal Laravel 13',
        'perubahan' => ['Judul', 'Status'],
        'alamat_ip' => '203.0.113.7',
        'created_at' => '2026-10-21 14:32:05',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.log-aktivitas.index'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSee('class="nav-link active" href="'.route('admin.log-aktivitas.index').'"', false)
        // Log ditulis otomatis, jadi tanpa tombol tambah, ubah, dan hapus.
        ->assertDontSee('tombol-tambah')
        ->assertDontSee('btn-warning')
        ->assertDontSee('btn-danger btn-ikon')
        // Satu baris filter: tanpa filter jenis data.
        ->assertDontSee('name="data"', false)
        // Alamat IP hanya di modal Lihat.
        ->assertDontSee('<th class="kolom-pas">Alamat IP</th>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"', 'name="q"',
            '<option value="">Semua pengguna</option>', '>Budi Santoso</option>',
            '<option value="">Semua aktivitas</option>', '<option value="aktifkan_kembali" >Aktifkan kembali</option>',
            '<div class="card-title">Log Aktivitas</div>',
            '<th class="kolom-pas">Waktu</th>', '<th class="kolom-pas">Pengguna</th>', '<th class="kolom-pas">Aktivitas</th>',
            '<th class="kolom-pas">Data</th>', '<th class="kolom-lebar">Nama Data</th>', '<th class="kolom-aksi">Aksi</th>',
            '<td class="kolom-pas">21 Oktober 2026, 14:32</td>', '<td class="kolom-pas">Budi Santoso</td>',
            '<td class="kolom-pas">Ubah</td>', '<td class="kolom-pas">Artikel</td>',
            '<div class="cell-strong">Mengenal Laravel 13</div>', '<div class="card-subtitle ringkasan-tabel">Judul, Status</div>',
            'data-detail-title="Detail Log Aktivitas"',
            '<th scope="row">Waktu</th>', '21 Oktober 2026, 14:32:05',
            '<th scope="row">Pengguna</th>', 'Budi Santoso', '<th scope="row">Email</th>', 'budi@example.com',
            '<th scope="row">Aktivitas</th>', 'Ubah', '<th scope="row">Data</th>', 'Artikel',
            '<th scope="row">Nama data</th>', 'Mengenal Laravel 13', '<th scope="row">Isian diubah</th>', 'Judul, Status',
            '<th scope="row">Alamat IP</th>', '<td>203.0.113.7</td>',
            // Masuk & keluar tidak menyangkut data.
            '<td class="kolom-pas">21 Oktober 2026, 08:15</td>', '<td class="kolom-pas">Masuk</td>', '<td class="kolom-pas">—</td>',
            '<th scope="row">Nama data</th>', '<td>—</td>', '<th scope="row">Isian diubah</th>', '<td>—</td>',
        ], false)
        ->assertDontSee('<div class="cell-strong">—</div>', false);
});

it('mencatat tambah, ubah, dan hapus data lewat panel admin', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.kategori-artikel.store'), ['nama_kategori' => 'Laravel'])
        ->assertSessionHasNoErrors();
    $kategori = KategoriArtikel::query()->sole();

    $this->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Laravel 13'])->assertSessionHasNoErrors();
    $this->delete(route('admin.kategori-artikel.destroy', $kategori));

    // Nama data = nama saat aksi terjadi; slug yang ikut berubah tidak dicatat.
    expect(ringkasanLog())->toEqual([
        [AksiAktivitas::Tambah, 'kategori_artikel', 'Laravel', null],
        [AksiAktivitas::Ubah, 'kategori_artikel', 'Laravel 13', ['Nama Kategori']],
        [AksiAktivitas::Hapus, 'kategori_artikel', 'Laravel 13', null],
    ])
        ->and(LogAktivitas::query()->pluck('id_pengguna')->unique()->all())->toBe([$this->admin->id_pengguna])
        ->and(LogAktivitas::query()->pluck('subjek_id')->unique()->all())->toBe([$kategori->id_kategori_artikel])
        ->and(LogAktivitas::query()->pluck('alamat_ip')->unique()->all())->toBe(['127.0.0.1']);
});

it('mencatat isian yang berubah seurut form', function () {
    Storage::fake('public');
    $artikel = Artikel::factory()->create(['judul' => 'Judul Lama', 'status_publikasi' => 'draf', 'tanggal' => '2026-10-09']);

    $this->actingAs($this->admin)
        ->put(route('admin.artikel.update', $artikel), [
            'id_kategori_artikel' => $artikel->id_kategori_artikel,
            'judul' => 'Mengenal Laravel 13',
            'deskripsi' => $artikel->deskripsi,
            'tanggal' => '2026-10-09',
            'status_publikasi' => 'terbit',
        ])
        ->assertSessionHasNoErrors();

    expect(ringkasanLog())->toEqual([[AksiAktivitas::Ubah, 'artikel', 'Mengenal Laravel 13', ['Judul', 'Status']]]);
});

it('tidak mencatat simpan tanpa perubahan maupun data yang dibuat di luar panel admin', function () {
    // Factory & seeder berjalan tanpa pengguna login.
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Laravel']);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Laravel'])
        ->assertSessionHasNoErrors();

    expect(LogAktivitas::count())->toBe(0);
});

it('mencatat penggantian kata sandi tanpa menyimpan kata sandinya', function () {
    $ani = Pengguna::factory()->administrator()->create(['nama' => 'Ani Lestari']);

    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $ani), [
            'nama' => 'Ani Lestari',
            'email' => $ani->email,
            'peran' => 'administrator',
            'password' => 'rahasiabaru1',
            'password_confirmation' => 'rahasiabaru1',
        ])
        ->assertSessionHasNoErrors();
    $this->put(route('admin.profil.update'), ['nama' => 'Budi S.', 'email' => 'budi@example.com'])->assertSessionHasNoErrors();

    expect(ringkasanLog())->toEqual([
        [AksiAktivitas::Ubah, 'pengguna', 'Ani Lestari', ['Kata Sandi']],
        // Profil Saya: pelaku dan datanya akun yang sama.
        [AksiAktivitas::Ubah, 'pengguna', 'Budi S.', ['Nama']],
    ])
        ->and(LogAktivitas::query()->toBase()->get()->toJson())
        ->not->toContain('rahasiabaru1')
        ->not->toContain($ani->refresh()->password);
});

it('mencatat pengguna yang dihapus lalu diaktifkan kembali', function () {
    $ani = Pengguna::factory()->administrator()->create(['nama' => 'Ani Lestari']);

    $this->actingAs($this->admin)->delete(route('admin.pengguna.destroy', $ani));
    $this->patch(route('admin.pengguna.aktifkan', $ani));

    // Kolom deleted_at yang dikosongkan saat diaktifkan kembali tidak dicatat sebagai Ubah.
    expect(ringkasanLog())->toEqual([
        [AksiAktivitas::Hapus, 'pengguna', 'Ani Lestari', null],
        [AksiAktivitas::AktifkanKembali, 'pengguna', 'Ani Lestari', null],
    ]);
});

it('mencatat gambar tambahan portofolio sebagai isian Gambar Tambahan', function () {
    Storage::fake('public');
    $kategori = KategoriPortofolio::factory()->create();
    $portofolio = Portofolio::factory()->for($kategori)->create(['judul' => 'Website Sekolah']);
    $lama = GambarPortofolio::factory()->for($portofolio)->create();
    $data = [
        'id_kategori_portofolio' => $kategori->id_kategori_portofolio,
        'judul' => 'Website Sekolah',
        'deskripsi' => $portofolio->deskripsi,
    ];
    $this->travel(1)->minutes();

    // Hanya gambar tambahan yang dibuang: tidak ada kolom portofolio yang berubah.
    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), [...$data, 'galeri_lama' => []])
        ->assertSessionHasNoErrors();
    // Judul dan gambar tambahan baru sekaligus.
    $this->put(route('admin.portofolio.update', $portofolio), [
        ...$data,
        'judul' => 'Website Sekolah Dasar',
        'galeri' => [UploadedFile::fake()->image('baru.png')],
    ])->assertSessionHasNoErrors();

    expect(ringkasanLog())->toEqual([
        [AksiAktivitas::Ubah, 'portofolio', 'Website Sekolah', ['Gambar Tambahan']],
        [AksiAktivitas::Ubah, 'portofolio', 'Website Sekolah Dasar', ['Judul', 'Gambar Tambahan']],
    ])
        ->and(GambarPortofolio::find($lama->id_gambar_portofolio))->toBeNull()
        // Perubahan gambar tambahan ikut memperbarui portofolionya (Terakhir diperbarui di dashboard, sitemap).
        ->and($portofolio->refresh()->updated_at->isSameMinute(now()))->toBeTrue();
});

it('mencatat masuk dan keluar panel admin, tetapi tidak percobaan masuk yang gagal', function () {
    $this->post(route('login.store'), ['email' => 'budi@example.com', 'password' => 'salah']);
    $this->post(route('login.store'), ['email' => 'budi@example.com', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));
    $this->post(route('logout'))->assertRedirect(route('login'));

    expect(ringkasanLog())->toEqual([
        [AksiAktivitas::Masuk, null, null, null],
        [AksiAktivitas::Keluar, null, null, null],
    ])
        ->and(LogAktivitas::query()->pluck('id_pengguna')->unique()->all())->toBe([$this->admin->id_pengguna])
        ->and(LogAktivitas::query()->pluck('alamat_ip')->unique()->all())->toBe(['127.0.0.1']);
});

it('memfilter log menurut nama data, pengguna, dan aktivitas', function () {
    $ani = Pengguna::factory()->create(['nama' => 'Ani Lestari']);
    $artikel = Artikel::factory()->create();
    $kategori = KategoriArtikel::factory()->create();
    LogAktivitas::factory()->for($ani, 'pengguna')->create();
    LogAktivitas::factory()->for($this->admin, 'pengguna')->create([
        'aksi' => AksiAktivitas::Ubah, 'subjek_type' => 'artikel', 'subjek_id' => $artikel->id_artikel,
        'label_subjek' => 'Mengenal Laravel 13', 'perubahan' => ['Judul'],
    ]);
    LogAktivitas::factory()->for($this->admin, 'pengguna')->create([
        'aksi' => AksiAktivitas::Hapus, 'subjek_type' => 'kategori_artikel', 'subjek_id' => $kategori->id_kategori_artikel,
        'label_subjek' => 'Pemrograman Web',
    ]);

    $daftar = fn (array $filter) => $this->actingAs($this->admin)->get(route('admin.log-aktivitas.index', $filter));

    $daftar(['q' => 'laravel'])
        ->assertSee('Mengenal Laravel 13')
        ->assertDontSee('Pemrograman Web</div>', false)
        ->assertDontSee('<td class="kolom-pas">Masuk</td>', false);
    $daftar(['pengguna' => $ani->id_pengguna])
        ->assertSee('<td class="kolom-pas">Masuk</td>', false)
        ->assertDontSee('Mengenal Laravel 13')
        ->assertSee('<option value="'.$ani->id_pengguna.'" selected>Ani Lestari</option>', false);
    $daftar(['aktivitas' => 'hapus'])
        ->assertSee('Pemrograman Web')
        ->assertDontSee('Mengenal Laravel 13')
        ->assertSee('<option value="hapus" selected>Hapus</option>', false);
    // Nilai filter yang tidak dikenal diabaikan.
    $daftar(['aktivitas' => 'cetak'])
        ->assertSee('Mengenal Laravel 13')
        ->assertSee('Pemrograman Web');
});

it('membedakan empty state log kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.log-aktivitas.index'))
        ->assertSee('Belum ada aktivitas')
        ->assertDontSee('<table class="table">', false);

    LogAktivitas::factory()->for($this->admin, 'pengguna')->create();

    $this->get(route('admin.log-aktivitas.index', ['q' => 'tidak-ada']))
        ->assertSee('Aktivitas tidak ditemukan')
        ->assertSee('Coba ubah kata kunci atau filter pencarian.');
});

it('memberi nama dan label isian form untuk setiap jenis data yang dicatat', function () {
    foreach (Relation::morphMap() as $alias => $kelas) {
        // Kolom yang diisi sistem, bukan isian form: slug, dan jenis modul (satu baris tetap per modul dari seeder).
        $kolomSistem = $kelas === ModulWebsite::class ? ['jenis'] : ['slug'];

        expect(class_uses_recursive($kelas))->toContain(MencatatAktivitas::class)
            ->and(defined($kelas.'::LABEL_LOG'))->toBeTrue("LABEL_LOG {$kelas}")
            ->and(LogAktivitas::labelJenis($alias))->toBe($kelas::LABEL_LOG)
            // Setiap isian form punya label.
            ->and(array_values(array_diff((new $kelas)->getFillable(), array_keys($kelas::ISIAN_LOG), $kolomSistem)))
            ->toBe([], "ISIAN_LOG {$kelas}");
    }

    expect(LogAktivitas::labelJenis('modul_lama'))->toBe('Modul Lama');
});
