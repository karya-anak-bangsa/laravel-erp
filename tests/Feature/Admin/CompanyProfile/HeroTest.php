<?php

use App\Models\CompanyProfile\Hero;
use App\Models\Pengguna;
use Database\Seeders\HeroDummySeeder;
use Database\Seeders\HeroSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

function dataHeroValid(array $timpa = []): array
{
    return [
        'judul' => 'Solusi Digital untuk Bisnis Anda',
        'deskripsi' => 'Website, mobile apps, dan pelatihan IT.',
        'gambar' => UploadedFile::fake()->image('hero.webp', 1536, 1024),
        'keyword' => ['Website', 'Mobile Apps'],
        'cta' => [
            ['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => 'primary'],
            ['label' => 'Lihat Portofolio', 'url' => '/portofolio', 'gaya' => 'secondary'],
        ],
        'status_aktif' => '1',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $hero = Hero::factory()->create();

    $this->get(route('admin.hero.index'))->assertRedirect(route('login'));
    $this->get(route('admin.hero.create'))->assertRedirect(route('login'));
    $this->post(route('admin.hero.store'), dataHeroValid())->assertRedirect(route('login'));
    $this->get(route('admin.hero.edit', $hero))->assertRedirect(route('login'));
    $this->delete(route('admin.hero.destroy', $hero))->assertRedirect(route('login'));
});

it('menampilkan daftar hero dengan hero aktif di baris pertama', function () {
    Hero::factory()->aktif()->create(['judul' => 'Hero Aktif Lama', 'created_at' => now()->subDay()]);
    Hero::factory()->create(['judul' => 'Hero Nonaktif Baru']);

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index'))
        ->assertOk()
        ->assertSeeInOrder(['Hero Aktif Lama', 'Hero Nonaktif Baru'])
        ->assertSee('class="nav-link active" href="'.route('admin.hero.index').'"', false);
});

it('menyusun halaman daftar: judul modul, kartu filter, lalu kartu tabel bertombol tambah', function () {
    Hero::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertDontSee('page-pretitle', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Hero</div>',
            'href="'.route('admin.hero.create').'"',
            '<table class="table">',
        ], false);
});

it('hanya menampilkan judul modul di page-header halaman tambah dan ubah', function () {
    $hero = Hero::factory()->create();

    foreach ([route('admin.hero.create'), route('admin.hero.edit', $hero)] as $url) {
        $this->actingAs($this->admin)
            ->get($url)
            ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
            ->assertDontSee('page-pretitle', false);
    }
});

it('menampilkan tombol ikon lihat, ubah, dan hapus beserta rincian untuk modal', function () {
    $hero = Hero::factory()->aktif()->create([
        'judul' => 'Solusi Digital',
        'deskripsi' => 'Website dan aplikasi.',
        'keyword' => ['Laravel', 'Flutter'],
        'cta' => [['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => 'primary']],
        'created_at' => '2026-10-09 08:30:00',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index'))
        ->assertSeeInOrder([
            'aria-label="Lihat"',
            'data-detail-title="Detail Hero"',
            '<template id="detail-',
            'Website dan aplikasi.',
            'Laravel, Flutter',
            'Hubungi Kami → #kontak (Utama)',
            '<span class="status status-green">Aktif</span>',
            '09 Oktober 2026, 08:30',
            Storage::disk('public')->url($hero->gambar),
            '</template>',
            'href="'.route('admin.hero.edit', $hero).'"',
            'aria-label="Ubah"',
            'action="'.route('admin.hero.destroy', $hero).'"',
            'aria-label="Hapus"',
        ], false);
});

it('mencari hero berdasarkan judul dan memfilter status', function () {
    Hero::factory()->aktif()->create(['judul' => 'Promo Bootcamp']);
    Hero::factory()->create(['judul' => 'Jasa Website']);

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index', ['q' => 'bootcamp']))
        ->assertSee('Promo Bootcamp')
        ->assertDontSee('Jasa Website');

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index', ['status' => 'nonaktif']))
        ->assertSee('Jasa Website')
        ->assertDontSee('Promo Bootcamp');
});

it('membedakan empty state data kosong dan hasil filter kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.hero.index'))
        ->assertSee('Belum ada hero')
        ->assertSee('Tambah Hero Pertama');

    Hero::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.hero.index', ['q' => 'tidak-ada']))
        ->assertSee('Hero tidak ditemukan');
});

it('mengaktifkan hero pertama secara bawaan di form tambah', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.hero.create'))
        ->assertOk()
        ->assertSee('name="status_aktif" value="1" checked', false)
        ->assertSee('data-repeater-maks="10"', false);

    Hero::factory()->aktif()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.hero.create'))
        ->assertDontSee('name="status_aktif" value="1" checked', false);
});

it('menambah hero beserta gambar, keyword, dan CTA', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.hero.store'), dataHeroValid([
            // Baris repeater yang dibiarkan kosong tidak ikut disimpan.
            'keyword' => ['Website', '', 'Mobile Apps', null],
            'cta' => [
                ['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => 'primary'],
                ['label' => '', 'url' => '', 'gaya' => 'secondary'],
            ],
        ]))
        ->assertRedirect(route('admin.hero.index'))
        ->assertSessionHas('success');

    $hero = Hero::sole();
    expect($hero->judul)->toBe('Solusi Digital untuk Bisnis Anda')
        ->and($hero->keyword)->toBe(['Website', 'Mobile Apps'])
        // MySQL mengurutkan ulang kunci objek JSON, jadi dibandingkan isinya saja.
        ->and($hero->cta)->toEqual([['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => 'primary']])
        ->and($hero->status_aktif)->toBeTrue()
        ->and($hero->gambar)->toMatch('#^company-profile/hero/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($hero->gambar);
});

it('boleh menyimpan hero tanpa tombol CTA', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.hero.store'), dataHeroValid(['cta' => null]))
        ->assertSessionHasNoErrors();

    expect(Hero::sole()->cta)->toBe([]);
});

it('menonaktifkan hero lain saat hero baru disimpan aktif', function () {
    $lama = Hero::factory()->aktif()->create();

    $this->actingAs($this->admin)->post(route('admin.hero.store'), dataHeroValid());

    expect($lama->refresh()->status_aktif)->toBeFalse()
        ->and(Hero::where('status_aktif', true)->count())->toBe(1);
});

it('menampilkan form ubah berisi keyword dan CTA tersimpan', function () {
    $hero = Hero::factory()->create([
        'keyword' => ['Sertifikasi IT'],
        'cta' => [['label' => 'Daftar Bootcamp', 'url' => '/bootcamp', 'gaya' => 'secondary']],
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.hero.edit', $hero))
        ->assertOk()
        ->assertSee('name="keyword[0]" value="Sertifikasi IT"', false)
        ->assertSee('name="cta[0][label]" value="Daftar Bootcamp"', false)
        ->assertSee('<option value="secondary" selected>', false)
        ->assertSee(Storage::disk('public')->url($hero->gambar));
});

it('memperbarui hero tanpa mengganti gambar', function () {
    $hero = Hero::factory()->create();
    $gambarLama = $hero->gambar;

    $this->actingAs($this->admin)
        ->put(route('admin.hero.update', $hero), dataHeroValid(['gambar' => null, 'judul' => 'Judul Baru', 'status_aktif' => '0']))
        ->assertRedirect(route('admin.hero.index'))
        ->assertSessionHasNoErrors();

    expect($hero->refresh()->judul)->toBe('Judul Baru')
        ->and($hero->gambar)->toBe($gambarLama)
        ->and($hero->status_aktif)->toBeFalse();
});

it('mengganti gambar hero dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/hero/lama.webp', 'isi');
    $hero = Hero::factory()->create(['gambar' => 'company-profile/hero/lama.webp']);

    $this->actingAs($this->admin)
        ->put(route('admin.hero.update', $hero), dataHeroValid(['gambar' => UploadedFile::fake()->image('baru.png')]))
        ->assertSessionHasNoErrors();

    $hero->refresh();
    expect($hero->gambar)->toMatch('#^company-profile/hero/[0-9a-f-]{36}\.png$#');
    Storage::disk('public')->assertExists($hero->gambar);
    Storage::disk('public')->assertMissing('company-profile/hero/lama.webp');
});

it('mengaktifkan hero yang diubah dan menonaktifkan hero lain', function () {
    $aktif = Hero::factory()->aktif()->create();
    $hero = Hero::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.hero.update', $hero), dataHeroValid(['gambar' => null, 'status_aktif' => '1']));

    expect($hero->refresh()->status_aktif)->toBeTrue()
        ->and($aktif->refresh()->status_aktif)->toBeFalse();
});

it('memvalidasi isian hero', function (array $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.hero.create'))
        ->post(route('admin.hero.store'), dataHeroValid($data))
        ->assertRedirect(route('admin.hero.create'))
        ->assertSessionHasErrors($kolom);

    expect(Hero::count())->toBe(0);
})->with([
    'judul kosong' => [['judul' => ''], 'judul'],
    'judul terlalu panjang' => [['judul' => str_repeat('a', 201)], 'judul'],
    'deskripsi kosong' => [['deskripsi' => ''], 'deskripsi'],
    'gambar kosong saat tambah' => [['gambar' => null], 'gambar'],
    'gambar bukan gambar' => [['gambar' => UploadedFile::fake()->create('hero.pdf', 10, 'application/pdf')], 'gambar'],
    'gambar lebih dari 2 MB' => [['gambar' => UploadedFile::fake()->image('hero.png')->size(2049)], 'gambar'],
    'keyword kosong semua' => [['keyword' => ['', null]], 'keyword'],
    'keyword lebih dari 10' => [['keyword' => array_map(fn ($i) => "Kata {$i}", range(1, 11))], 'keyword'],
    'keyword terlalu panjang' => [['keyword' => [str_repeat('a', 51)]], 'keyword.0'],
    'keyword berulang' => [['keyword' => ['Website', 'website']], 'keyword.1'],
    'cta lebih dari 3' => [['cta' => array_fill(0, 4, ['label' => 'Tombol', 'url' => '#a', 'gaya' => 'primary'])], 'cta'],
    'label cta kosong' => [['cta' => [['label' => '', 'url' => '#kontak', 'gaya' => 'primary']]], 'cta.0.label'],
    'url cta kosong' => [['cta' => [['label' => 'Tombol', 'url' => '', 'gaya' => 'primary']]], 'cta.0.url'],
    'url cta javascript' => [['cta' => [['label' => 'Tombol', 'url' => 'javascript:alert(1)', 'gaya' => 'primary']]], 'cta.0.url'],
    'url cta protocol-relative' => [['cta' => [['label' => 'Tombol', 'url' => '//contoh.com', 'gaya' => 'primary']]], 'cta.0.url'],
    'url cta berspasi' => [['cta' => [['label' => 'Tombol', 'url' => '/halaman saya', 'gaya' => 'primary']]], 'cta.0.url'],
    'gaya cta tidak dikenal' => [['cta' => [['label' => 'Tombol', 'url' => '#kontak', 'gaya' => 'danger']]], 'cta.0.gaya'],
]);

it('menerima berbagai bentuk URL tombol CTA', function (string $url) {
    $this->actingAs($this->admin)
        ->post(route('admin.hero.store'), dataHeroValid(['cta' => [['label' => 'Tombol', 'url' => $url, 'gaya' => 'primary']]]))
        ->assertSessionHasNoErrors();
})->with(['#kontak', '/portofolio', 'https://wa.me/6281234567890', 'mailto:info@karyaanakbangsa.co.id', 'tel:+6281234567890']);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.hero.store'), dataHeroValid([
            'judul' => '',
            'keyword' => [],
            'cta' => [['label' => 'Tombol', 'url' => 'javascript:alert(1)', 'gaya' => 'primary']],
        ]))
        ->assertSessionHasErrors([
            'judul' => 'Kolom judul wajib diisi.',
            'keyword' => 'Isi minimal satu keyword.',
            'cta.0.url' => 'URL tombol harus diawali /, #, https://, mailto:, atau tel: dan tanpa spasi.',
        ]);
});

it('menghapus hero secara soft delete tanpa menghapus gambarnya', function () {
    Storage::disk('public')->put('company-profile/hero/tetap.webp', 'isi');
    $hero = Hero::factory()->create(['gambar' => 'company-profile/hero/tetap.webp']);

    $this->actingAs($this->admin)
        ->delete(route('admin.hero.destroy', $hero))
        ->assertRedirect(route('admin.hero.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($hero);
    Storage::disk('public')->assertExists('company-profile/hero/tetap.webp');
});

it('membuat satu hero awal yang aktif beserta gambarnya dari seeder', function () {
    $this->seed(HeroSeeder::class);

    $hero = Hero::sole();
    expect($hero->status_aktif)->toBeTrue()
        ->and($hero->keyword)->toContain('Bootcamp')
        ->and($hero->cta)->toHaveCount(2)
        ->and($hero->gambar)->toEndWith('.webp');
    Storage::disk('public')->assertExists($hero->gambar);
});

it('menambah hero dummy nonaktif sehingga tetap hanya satu hero aktif', function () {
    $this->seed([HeroSeeder::class, HeroDummySeeder::class]);

    expect(Hero::count())->toBe(5)
        ->and(Hero::where('status_aktif', true)->count())->toBe(1)
        ->and(Hero::where('status_aktif', true)->value('judul'))->toBe('Solusi Digital & Talenta IT untuk Indonesia');
    Hero::all()->each(fn (Hero $hero) => Storage::disk('public')->assertExists($hero->gambar));
});

it('menjalankan seeder hero dummy ulang tanpa duplikasi data maupun gambar', function () {
    $this->seed([HeroSeeder::class, HeroDummySeeder::class]);
    $this->seed(HeroDummySeeder::class);

    expect(Hero::count())->toBe(5)
        ->and(Storage::disk('public')->allFiles(Hero::FOLDER))->toHaveCount(5);
});

it('menjalankan seeder hero ulang tanpa menimpa isian admin', function () {
    $this->seed(HeroSeeder::class);
    Hero::query()->update(['judul' => 'Judul dari Admin']);

    $this->seed(HeroSeeder::class);

    expect(Hero::count())->toBe(1)
        ->and(Hero::first()->judul)->toBe('Judul dari Admin');
});
