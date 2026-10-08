<?php

use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use Database\Seeders\PortofolioDummySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

function dataPortofolioValid(array $timpa = []): array
{
    return [
        'judul' => 'Website Profil Sekolah',
        'kategori' => 'Website',
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
    Portofolio::factory()->create(['judul' => 'Proyek Baru']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertOk()
        ->assertSeeInOrder(['Proyek Baru', 'Proyek Lama'])
        ->assertSee('class="nav-link active" href="'.route('admin.portofolio.index').'"', false);
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
    $portofolio = Portofolio::factory()->create([
        'judul' => 'Aplikasi Klinik',
        'slug' => 'aplikasi-klinik',
        'kategori' => 'Mobile Apps',
        'deskripsi' => 'Antrean pasien dari rumah.',
        'created_at' => '2026-10-09 08:30:00',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSeeInOrder([
            'aria-label="Lihat"',
            'data-detail-title="Detail Portofolio"',
            '<template id="detail-',
            'Mobile Apps',
            '/portofolio/aplikasi-klinik',
            'Antrean pasien dari rumah.',
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

it('memfilter portofolio berdasarkan kategori yang tersedia', function () {
    Portofolio::factory()->create(['judul' => 'Website Sekolah', 'kategori' => 'Website']);
    Portofolio::factory()->create(['judul' => 'Aplikasi Klinik', 'kategori' => 'Mobile Apps']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['kategori' => 'Mobile Apps']))
        ->assertSee('Aplikasi Klinik')
        ->assertDontSee('Website Sekolah')
        ->assertSee('<option value="Mobile Apps" selected>', false)
        ->assertSeeInOrder(['Semua kategori', 'Mobile Apps', 'Website']);
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSee('Belum ada portofolio')
        ->assertSee('Tambah Portofolio Pertama');

    Portofolio::factory()->create(['kategori' => 'Website']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['q' => 'tidak-ada']))
        ->assertSee('Portofolio tidak ditemukan');

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index', ['kategori' => 'Tidak Ada']))
        ->assertSee('Portofolio tidak ditemukan');
});

it('menyarankan kategori yang sudah ada di form tambah', function () {
    Portofolio::factory()->create(['kategori' => 'Mobile Apps']);
    Portofolio::factory()->create(['kategori' => 'Mobile Apps']);

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.portofolio.create'))
        ->assertOk()
        ->assertSee('list="saran-kategori"', false)
        ->assertSee('<option value="Mobile Apps"></option>', false);

    expect(substr_count($respons->getContent(), '<option value="Mobile Apps"></option>'))->toBe(1);
});

it('menambah portofolio beserta slug dan gambarnya', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid())
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHas('success');

    $portofolio = Portofolio::sole();
    expect($portofolio->judul)->toBe('Website Profil Sekolah')
        ->and($portofolio->slug)->toBe('website-profil-sekolah')
        ->and($portofolio->kategori)->toBe('Website')
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
    $portofolio = Portofolio::factory()->create(['judul' => 'Aplikasi Klinik', 'slug' => 'aplikasi-klinik', 'kategori' => 'Mobile Apps']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.edit', $portofolio))
        ->assertOk()
        ->assertSee('value="Aplikasi Klinik"', false)
        ->assertSee('value="Mobile Apps"', false)
        ->assertSee('/portofolio/aplikasi-klinik')
        ->assertSee(Storage::disk('public')->url($portofolio->gambar));
});

it('memperbarui portofolio tanpa mengganti gambar dan menyesuaikan slug dengan judul baru', function () {
    $portofolio = Portofolio::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);
    $gambarLama = $portofolio->gambar;

    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid(['judul' => 'Judul Baru', 'gambar' => null]))
        ->assertRedirect(route('admin.portofolio.index'))
        ->assertSessionHasNoErrors();

    expect($portofolio->refresh()->judul)->toBe('Judul Baru')
        ->and($portofolio->slug)->toBe('judul-baru')
        ->and($portofolio->gambar)->toBe($gambarLama);
});

it('mengganti gambar portofolio dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/portofolio/lama.webp', 'isi');
    $portofolio = Portofolio::factory()->create(['gambar' => 'company-profile/portofolio/lama.webp']);

    $this->actingAs($this->admin)
        ->put(route('admin.portofolio.update', $portofolio), dataPortofolioValid(['gambar' => UploadedFile::fake()->image('baru.png')]))
        ->assertSessionHasNoErrors();

    expect($portofolio->refresh()->gambar)->toMatch('#^company-profile/portofolio/[0-9a-f-]{36}\.png$#');
    Storage::disk('public')->assertMissing('company-profile/portofolio/lama.webp');
});

it('memvalidasi isian portofolio', function (array $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.portofolio.create'))
        ->post(route('admin.portofolio.store'), dataPortofolioValid($data))
        ->assertRedirect(route('admin.portofolio.create'))
        ->assertSessionHasErrors($kolom);

    expect(Portofolio::count())->toBe(0);
})->with([
    'judul kosong' => [['judul' => ''], 'judul'],
    'judul terlalu panjang' => [['judul' => str_repeat('a', 201)], 'judul'],
    'kategori kosong' => [['kategori' => ''], 'kategori'],
    'kategori terlalu panjang' => [['kategori' => str_repeat('a', 51)], 'kategori'],
    'deskripsi kosong' => [['deskripsi' => ''], 'deskripsi'],
    'deskripsi terlalu panjang' => [['deskripsi' => str_repeat('a', 5001)], 'deskripsi'],
    'gambar kosong saat tambah' => [['gambar' => null], 'gambar'],
    'gambar bukan gambar' => [['gambar' => UploadedFile::fake()->create('portofolio.pdf', 10, 'application/pdf')], 'gambar'],
    'gambar lebih dari 2 MB' => [['gambar' => UploadedFile::fake()->image('portofolio.png')->size(2049)], 'gambar'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.portofolio.store'), dataPortofolioValid(['judul' => '', 'kategori' => str_repeat('a', 51)]))
        ->assertSessionHasErrors([
            'judul' => 'Kolom judul wajib diisi.',
            'kategori' => 'Kolom kategori tidak boleh lebih dari 50 karakter.',
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
    $this->seed(PortofolioDummySeeder::class);
    $this->seed(PortofolioDummySeeder::class);

    expect(Portofolio::count())->toBe(6)
        ->and(Portofolio::pluck('slug')->unique())->toHaveCount(6);
    Portofolio::all()->each(fn (Portofolio $portofolio) => Storage::disk('public')->assertExists($portofolio->gambar));
});
