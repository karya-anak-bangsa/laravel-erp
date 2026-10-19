<?php

use App\Http\Requests\Admin\CompanyProfile\StorePortofolioRequest;
use App\Models\CompanyProfile\GambarPortofolio;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use Database\Seeders\KategoriPortofolioSeeder;
use Database\Seeders\PortofolioDummySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
    $this->kategori = KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);
});

function dataPortofolioValid(array $timpa = []): array
{
    return [
        'id_kategori_portofolio' => KategoriPortofolio::query()->value('id_kategori_portofolio'),
        'judul' => 'Website Profil Sekolah',
        'deskripsi' => 'Website profil sekolah dengan panel admin.',
        'gambar' => UploadedFile::fake()->image('portofolio.webp', 800, 600),
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $portofolio = Portofolio::factory()->create();

    $this->get(route('admin.portofolio.index'))->assertRedirect(route('login'));
    $this->get(route('admin.portofolio.create'))->assertRedirect(route('login'));
    $this->post(route('admin.portofolio.store'), dataPortofolioValid())->assertRedirect(route('login'));
    $this->get(route('admin.portofolio.edit', $portofolio))->assertRedirect(route('login'));
    $this->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid())->assertRedirect(route('login'));
    $this->delete(route('admin.portofolio.destroy', $portofolio))->assertRedirect(route('login'));
});

it('menampilkan daftar portofolio terbaru lebih dulu', function () {
    Portofolio::factory()->create(['judul' => 'Proyek Lama', 'created_at' => now()->subDay()]);
    // Dibuat dalam detik yang sama seperti data seeder: yang disimpan belakangan tetap di atas, sama dengan website.
    Portofolio::factory()->create(['judul' => 'Proyek Baru Pertama', 'created_at' => now()]);
    Portofolio::factory()->create(['judul' => 'Proyek Baru Kedua', 'created_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertOk()
        ->assertSeeInOrder(['Proyek Baru Kedua', 'Proyek Baru Pertama', 'Proyek Lama'])
        ->assertSee('class="nav-sublink active" href="'.route('admin.portofolio.index').'"', false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    Portofolio::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Portofolio</div>',
            'href="'.route('admin.portofolio.create').'"',
            '<table class="table">',
        ], false);
});

it('menampilkan tombol ikon lihat, ubah, dan hapus beserta rincian untuk modal', function () {
    $portofolio = Portofolio::factory()->kategori('Mobile Apps')->create([
        'judul' => 'Aplikasi Klinik',
        'slug' => 'aplikasi-klinik',
        'deskripsi' => 'Antrean pasien dari rumah.',
        'created_at' => '2026-10-09 08:30:00',
    ]);
    GambarPortofolio::factory()->count(2)->for($portofolio)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSeeInOrder([
            'aria-label="Lihat"',
            'data-detail-title="Detail Portofolio"',
            '<template id="detail-',
            'Mobile Apps',
            '/portofolio/aplikasi-klinik',
            'href="'.route('portofolio.show', 'aplikasi-klinik').'" target="_blank" rel="noopener"',
            'Buka halaman',
            'Antrean pasien dari rumah.',
            '<th scope="row">Gambar tambahan</th>',
            '<td>2 gambar</td>',
            '09 Oktober 2026, 08:30',
            Storage::disk('public')->url($portofolio->gambar),
            '</template>',
            'href="'.route('admin.portofolio.edit', $portofolio).'"',
            'aria-label="Ubah"',
            'action="'.route('admin.portofolio.destroy', $portofolio).'"',
            'aria-label="Hapus"',
        ], false);
});

it('mencari portofolio berdasarkan judul atau deskripsi', function () {
    Portofolio::factory()->create(['judul' => 'Website Sekolah', 'deskripsi' => 'Profil sekolah.']);
    Portofolio::factory()->create(['judul' => 'Aplikasi Klinik', 'deskripsi' => 'Antrean pasien Android.']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['q' => 'android']))
        ->assertSee('Aplikasi Klinik')
        ->assertDontSee('Website Sekolah');
});

it('memfilter portofolio berdasarkan kategori, dengan pilihan semua kategori urut abjad', function () {
    $mobile = KategoriPortofolio::factory()->create(['nama_kategori' => 'Mobile Apps']);
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Bootcamp']);
    Portofolio::factory()->for($this->kategori)->create(['judul' => 'Website Sekolah']);
    Portofolio::factory()->for($mobile)->create(['judul' => 'Aplikasi Klinik']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['kategori' => $mobile->id_kategori_portofolio]))
        ->assertSee('Aplikasi Klinik')
        ->assertDontSee('Website Sekolah')
        ->assertSee('<option value="'.$mobile->id_kategori_portofolio.'" selected>Mobile Apps</option>', false)
        // Kategori tanpa portofolio tetap bisa dipilih.
        ->assertSeeInOrder(['Semua kategori', 'Bootcamp', 'Mobile Apps', 'Website']);
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSee('Belum ada portofolio')
        ->assertDontSee('Tambah Portofolio Pertama')
        ->assertSee('<a href="'.route('admin.portofolio.create').'" class="btn btn-success tombol-tambah"', false);

    Portofolio::factory()->for($this->kategori)->create();
    $kategoriKosong = KategoriPortofolio::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['q' => 'tidak-ada']))
        ->assertSee('Portofolio tidak ditemukan');

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['kategori' => $kategoriKosong->id_kategori_portofolio]))
        ->assertSee('Portofolio tidak ditemukan');
});

it('menampilkan pilihan kategori urut abjad di form tambah', function () {
    $mobile = KategoriPortofolio::factory()->create(['nama_kategori' => 'Mobile Apps']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.create'))
        ->assertOk()
        ->assertSee('<select id="id_kategori_portofolio" name="id_kategori_portofolio"', false)
        ->assertSeeInOrder([
            '<option value="">— Pilih —</option>',
            '<option value="'.$mobile->id_kategori_portofolio.'" >Mobile Apps</option>',
            '<option value="'.$this->kategori->id_kategori_portofolio.'" >Website</option>',
        ], false)
        ->assertDontSee('Belum ada kategori.');
});

it('mengarahkan ke menu Kategori Portofolio bila belum ada kategori', function () {
    $this->kategori->forceDelete();

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.create'))
        ->assertSee('Belum ada kategori. Tambahkan lebih dulu lewat menu Kategori Portofolio.');
});

it('menambah portofolio beserta slug dan gambarnya', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid())
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHas('success');

    $portofolio = Portofolio::sole();
    expect($portofolio->judul)->toBe('Website Profil Sekolah')
        ->and($portofolio->slug)->toBe('website-profil-sekolah')
        ->and($portofolio->id_kategori_portofolio)->toBe($this->kategori->id_kategori_portofolio)
        ->and($portofolio->gambar)->toMatch('#^company-profile/portofolio/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($portofolio->gambar);
});

it('memberi akhiran angka pada slug bila judul sudah dipakai', function () {
    Portofolio::factory()->create(['judul' => 'Website Profil Sekolah', 'slug' => 'website-profil-sekolah']);

    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid())
        ->assertSessionHasNoErrors();

    expect(Portofolio::latest('id_portofolio')->first()->slug)->toBe('website-profil-sekolah-2');
});

it('menampilkan form ubah berisi data tersimpan dan alamat detail', function () {
    $mobile = KategoriPortofolio::factory()->create(['nama_kategori' => 'Mobile Apps']);
    $portofolio = Portofolio::factory()->for($mobile)->create(['judul' => 'Aplikasi Klinik', 'slug' => 'aplikasi-klinik']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.edit', $portofolio))
        ->assertOk()
        ->assertSee('value="Aplikasi Klinik"', false)
        ->assertSee('<option value="'.$mobile->id_kategori_portofolio.'" selected>Mobile Apps</option>', false)
        ->assertSee('/portofolio/aplikasi-klinik — tetap meskipun judul diubah')
        ->assertSee(Storage::disk('public')->url($portofolio->gambar));
});

it('memperbarui portofolio tanpa mengganti gambar maupun slugnya', function () {
    $portofolio = Portofolio::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);
    $gambarLama = $portofolio->gambar;

    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid(['judul' => 'Judul Baru', 'gambar' => null]))
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHasNoErrors();

    expect($portofolio->refresh()->judul)->toBe('Judul Baru')
        ->and($portofolio->slug)->toBe('judul-lama')
        ->and($portofolio->gambar)->toBe($gambarLama);
});

it('mengganti gambar portofolio dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/portofolio/lama.webp', 'isi');
    $portofolio = Portofolio::factory()->create(['gambar' => 'company-profile/portofolio/lama.webp']);

    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid(['gambar' => UploadedFile::fake()->image('baru.png')]))
        ->assertSessionHasNoErrors();

    expect($portofolio->refresh()->gambar)->toMatch('#^company-profile/portofolio/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertMissing('company-profile/portofolio/lama.webp');
});

it('menambah portofolio beserta gambar tambahannya urut unggah', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid([
            'galeri' => [UploadedFile::fake()->image('kedua.png', 300, 200), UploadedFile::fake()->image('ketiga.jpg', 200, 300)],
        ]))
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHasNoErrors();

    // Urutan dikenali dari ukurannya karena format berkas bisa berubah menjadi WebP saat dioptimalkan.
    $ukuran = fn (GambarPortofolio $item) => array_slice(getimagesizefromstring((string) Storage::disk('public')->get($item->gambar)), 0, 2);
    $gambar = Portofolio::sole()->gambarPortofolio;
    expect($gambar)->toHaveCount(2)
        ->and($gambar->map($ukuran)->all())->toBe([[300, 200], [200, 300]]);
});

it('mengabaikan baris gambar tambahan yang belum diberi berkas', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid([
            'galeri' => [null, UploadedFile::fake()->image('kedua.png'), null],
        ]))
        ->assertSessionHasNoErrors();

    expect(Portofolio::sole()->gambarPortofolio)->toHaveCount(1);
});

it('menolak lebih dari empat gambar tambahan saat menambah', function () {
    $galeri = array_map(fn (int $nomor) => UploadedFile::fake()->image("galeri-{$nomor}.png"), range(1, StorePortofolioRequest::MAKS_GAMBAR));

    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid(['galeri' => $galeri]))
        ->assertSessionHasErrors(['galeri' => 'Setiap portofolio paling banyak 5 gambar termasuk gambar utama, jadi gambar tambahan paling banyak 4.']);

    expect(Portofolio::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles(Portofolio::FOLDER))->toBe([]);
});

it('menampilkan gambar tambahan sebagai repeater dengan baris gambar tersimpan di form ubah', function () {
    $portofolio = Portofolio::factory()->create();
    $gambar = GambarPortofolio::factory()->for($portofolio)->create();

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.portofolio.edit', $portofolio))
        ->assertOk()
        ->assertSeeInOrder([
            'data-repeater data-repeater-maks="4"',
            Storage::disk('public')->url($gambar->gambar),
            'name="galeri_lama[0]" value="'.$gambar->id_gambar_portofolio.'"',
            'aria-label="Buang gambar"',
            'Belum ada gambar tambahan.',
            '<template data-repeater-templat>',
            'name="galeri[__i__]"',
            'Tambah Gambar',
        ], false)
        ->assertDontSee('multiple', false);

    expect($respons->getContent())->toMatch('/data-repeater-kosong\s+hidden/');
});

it('tidak memunculkan lagi baris gambar tambahan yang sudah dibuang setelah validasi gagal', function () {
    $portofolio = Portofolio::factory()->create();
    $dibuang = GambarPortofolio::factory()->for($portofolio)->create();
    $tetap = GambarPortofolio::factory()->for($portofolio)->create();

    $this->actingAs($this->admin)
        ->withSession(['_old_input' => ['judul' => '', 'galeri_lama' => [(string) $tetap->id_gambar_portofolio]]])
        ->get(route('admin.portofolio.edit', $portofolio))
        ->assertSee('value="'.$tetap->id_gambar_portofolio.'"', false)
        ->assertDontSee(Storage::disk('public')->url($dibuang->gambar), false);
});

it('membuang gambar tambahan yang barisnya dibuang beserta berkasnya dan menambah gambar baru di akhir', function () {
    $portofolio = Portofolio::factory()->create();
    Storage::disk('public')->put('company-profile/portofolio/buang.webp', 'isi');
    Storage::disk('public')->put('company-profile/portofolio/tetap.webp', 'isi');
    $dibuang = GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/buang.webp']);
    $tetap = GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/tetap.webp']);

    // Indeks mengikuti baris repeater: gambar lama di baris 1, unggahan baru di baris 2 & 3 (baris 3 belum diberi berkas).
    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid([
            'gambar' => null,
            'galeri_lama' => [1 => $tetap->id_gambar_portofolio],
            'galeri' => [2 => UploadedFile::fake()->image('baru.png'), 3 => null],
        ]))
        ->assertSessionHasNoErrors();

    $gambar = $portofolio->refresh()->gambarPortofolio;
    expect($gambar)->toHaveCount(2)
        ->and($gambar[0]->is($tetap))->toBeTrue()
        ->and($gambar[1]->gambar)->toMatch('#^company-profile/portofolio/[0-9a-f-]{36}\.#')
        ->and(GambarPortofolio::find($dibuang->id_gambar_portofolio))->toBeNull();
    Storage::disk('public')->assertMissing('company-profile/portofolio/buang.webp');
    Storage::disk('public')->assertExists('company-profile/portofolio/tetap.webp');
    Storage::disk('public')->assertExists($gambar[1]->gambar);
});

it('menghitung gambar tambahan lama saat membatasi unggahan baru di form ubah', function () {
    $portofolio = Portofolio::factory()->create();
    $lama = GambarPortofolio::factory()->count(3)->for($portofolio)->create();
    $baru = fn () => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.png')];

    // 3 lama + 2 baru = 5 gambar tambahan, melewati batas 4.
    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid([
            'gambar' => null,
            'galeri_lama' => $lama->modelKeys(),
            'galeri' => $baru(),
        ]))
        ->assertSessionHasErrors(['galeri' => 'Setiap portofolio paling banyak 5 gambar termasuk gambar utama, jadi gambar tambahan paling banyak 4.']);
    expect($portofolio->gambarPortofolio()->count())->toBe(3);

    // Membuang satu gambar lama memberi ruang untuk satu unggahan lagi: 2 lama + 2 baru = 4.
    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid([
            'gambar' => null,
            'galeri_lama' => [$lama[1]->id_gambar_portofolio, $lama[2]->id_gambar_portofolio],
            'galeri' => $baru(),
        ]))
        ->assertSessionHasNoErrors();
    expect($portofolio->gambarPortofolio()->count())->toBe(4);
});

it('menghapus semua gambar tambahan bila semua barisnya dibuang', function () {
    $portofolio = Portofolio::factory()->create();
    Storage::disk('public')->put('company-profile/portofolio/buang.webp', 'isi');
    GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/buang.webp']);

    // Tanpa baris, galeri_lama tidak terkirim sama sekali.
    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid(['gambar' => null]))
        ->assertSessionHasNoErrors();

    expect($portofolio->gambarPortofolio()->count())->toBe(0);
    Storage::disk('public')->assertMissing('company-profile/portofolio/buang.webp');
});

it('menolak mempertahankan gambar tambahan milik portofolio lain', function () {
    $portofolio = Portofolio::factory()->create();
    $milikLain = GambarPortofolio::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid([
            'gambar' => null,
            'galeri_lama' => [$milikLain->id_gambar_portofolio],
        ]))
        ->assertSessionHasErrors('galeri_lama.0');

    expect(GambarPortofolio::find($milikLain->id_gambar_portofolio))->not->toBeNull();
});

it('memvalidasi isian portofolio', function (Closure $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.portofolio.create'))
        ->post(route('admin.portofolio.store'), dataPortofolioValid($data()))
        ->assertRedirect(route('admin.portofolio.create'))
        ->assertSessionHasErrors($kolom);

    expect(Portofolio::count())->toBe(0);
})->with([
    'judul kosong' => [fn () => ['judul' => ''], 'judul'],
    'judul terlalu panjang' => [fn () => ['judul' => str_repeat('a', 201)], 'judul'],
    'kategori kosong' => [fn () => ['id_kategori_portofolio' => ''], 'id_kategori_portofolio'],
    'kategori tidak ada' => [fn () => ['id_kategori_portofolio' => 999999], 'id_kategori_portofolio'],
    'kategori sudah dihapus' => [fn () => ['id_kategori_portofolio' => tap(KategoriPortofolio::factory()->create())->delete()->id_kategori_portofolio], 'id_kategori_portofolio'],
    'deskripsi kosong' => [fn () => ['deskripsi' => ''], 'deskripsi'],
    'deskripsi terlalu panjang' => [fn () => ['deskripsi' => str_repeat('a', 5001)], 'deskripsi'],
    'gambar kosong saat tambah' => [fn () => ['gambar' => null], 'gambar'],
    'gambar bukan gambar' => [fn () => ['gambar' => UploadedFile::fake()->create('portofolio.pdf', 10, 'application/pdf')], 'gambar'],
    'gambar lebih dari 2 MB' => [fn () => ['gambar' => UploadedFile::fake()->image('portofolio.png')->size(2049)], 'gambar'],
    'gambar tambahan bukan gambar' => [fn () => ['galeri' => [UploadedFile::fake()->create('galeri.pdf', 10, 'application/pdf')]], 'galeri.0'],
    'gambar tambahan lebih dari 2 MB' => [fn () => ['galeri' => [UploadedFile::fake()->image('galeri.png')->size(2049)]], 'galeri.0'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid(['judul' => '', 'id_kategori_portofolio' => 999999]))
        ->assertSessionHasErrors([
            'judul' => 'Kolom judul wajib diisi.',
            'id_kategori_portofolio' => 'Kategori yang dipilih tidak valid.',
        ]);
});

it('menghapus portofolio secara soft delete tanpa menghapus gambarnya', function () {
    Storage::disk('public')->put('company-profile/portofolio/tetap.webp', 'isi');
    $portofolio = Portofolio::factory()->create(['gambar' => 'company-profile/portofolio/tetap.webp']);

    $this->actingAs($this->admin)
        ->delete(route('admin.portofolio.destroy', $portofolio))
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($portofolio);
    Storage::disk('public')->assertExists('company-profile/portofolio/tetap.webp');
});

it('membuat portofolio contoh dari seeder dummy tanpa duplikat saat dijalankan ulang', function () {
    $this->kategori->forceDelete();
    $this->seed(KategoriPortofolioSeeder::class);
    $this->seed(PortofolioDummySeeder::class);
    $this->seed(PortofolioDummySeeder::class);

    expect(Portofolio::count())->toBe(7)
        ->and(Portofolio::pluck('slug')->unique())->toHaveCount(7)
        ->and(GambarPortofolio::count())->toBe(7 * (StorePortofolioRequest::MAKS_GAMBAR - 1))
        // Setiap kategori awal punya contoh, dicocokkan lewat nama dari KategoriPortofolioSeeder.
        ->and(KategoriPortofolio::withCount('portofolio')->orderBy('id_kategori_portofolio')->pluck('portofolio_count', 'nama_kategori')->all())
        ->toBe(['Website' => 2, 'Mobile Apps' => 2, 'Pelatihan IT' => 1, 'Sertifikasi IT' => 1, 'Bootcamp' => 1]);
    Portofolio::with('gambarPortofolio')->get()->each(function (Portofolio $portofolio) {
        // Setiap portofolio contoh berisi gambar penuh (pilihan pemilik 2026-10-18).
        expect($portofolio->gambarPortofolio)->toHaveCount(StorePortofolioRequest::MAKS_GAMBAR - 1);
        Storage::disk('public')->assertExists($portofolio->gambar);
        $portofolio->gambarPortofolio->each(fn (GambarPortofolio $gambar) => Storage::disk('public')->assertExists($gambar->gambar));
    });
});
