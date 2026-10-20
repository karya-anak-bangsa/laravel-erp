<?php

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;
use App\Services\Shared\HtmlSanitizerService;
use App\Support\TeksHtml;
use Database\Seeders\KategoriTutorialSeeder;
use Database\Seeders\TutorialDummySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
    $this->kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);
});

function dataTutorialValid(array $timpa = []): array
{
    return [
        'id_kategori_tutorial' => KategoriTutorial::query()->value('id_kategori_tutorial'),
        'judul' => 'Tutorial Laravel 13',
        'deskripsi' => '<p>Membangun aplikasi data buku.</p>',
        'gambar' => UploadedFile::fake()->image('sampul.webp', 1200, 630),
        'status_publikasi' => 'draf',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $tutorial = Tutorial::factory()->create();

    $this->get(route('admin.tutorial.index'))->assertRedirect(route('login'));
    $this->get(route('admin.tutorial.create'))->assertRedirect(route('login'));
    $this->post(route('admin.tutorial.store'), dataTutorialValid())->assertRedirect(route('login'));
    $this->get(route('admin.tutorial.edit', $tutorial))->assertRedirect(route('login'));
    $this->put(route('admin.tutorial.update', $tutorial), dataTutorialValid())->assertRedirect(route('login'));
    $this->delete(route('admin.tutorial.destroy', $tutorial))->assertRedirect(route('login'));
});

it('menampilkan tutorial terbaru lebih dulu beserta kategori, jumlah materi, dan status', function () {
    $lama = Tutorial::factory()->terbit()->kategori('Basis Data')->create(['judul' => 'Tutorial lama']);
    MateriTutorial::factory()->count(3)->for($lama)->create();
    Tutorial::factory()->draf()->kategori('Pemrograman Mobile')->create(['judul' => 'Tutorial terbaru']);

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Tutorial terbaru', 'Pemrograman Mobile', '<td style="text-align:center">0</td>', 'status-yellow">Draf',
            'Tutorial lama', 'Basis Data', '<td style="text-align:center">3</td>', 'status-green">Terbit',
        ], false)
        ->assertSee('class="nav-sublink active" href="'.route('admin.tutorial.index').'"', false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin dengan rincian untuk modal', function () {
    $tutorial = Tutorial::factory()->create(['deskripsi' => '<p>Pengantar <strong>seri</strong></p>']);
    MateriTutorial::factory()->count(2)->for($tutorial)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSee('<div class="card-subtitle">Pengantar seri</div>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Daftar Tutorial</div>',
            'href="'.route('admin.tutorial.create').'"',
            'data-detail-title="Detail Tutorial"',
            'href="'.route('admin.materi-tutorial.index', ['tutorial' => $tutorial->id_tutorial]).'"',
            '<td class="konten-html"><p>Pengantar <strong>seri</strong></p></td>',
            Storage::disk('public')->url($tutorial->gambar),
            'href="'.route('admin.tutorial.edit', $tutorial).'"',
            'action="'.route('admin.tutorial.destroy', $tutorial).'"',
        ], false)
        // Konfirmasi hapus menyebut jumlah materi yang ikut terhapus.
        ->assertSee('beserta 2 materinya tidak akan tampil lagi', false);
});

it('menyediakan tombol Buka halaman di modal detail hanya untuk tutorial yang tayang', function () {
    Tutorial::factory()->terbit()->has(MateriTutorial::factory(), 'materiTutorial')->create(['slug' => 'sudah-tayang']);
    Tutorial::factory()->terbit()->create(['slug' => 'tanpa-materi']);
    Tutorial::factory()->draf()->has(MateriTutorial::factory(), 'materiTutorial')->create(['slug' => 'masih-draf']);

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index'))
        ->assertSee('/tutorial/sudah-tayang')
        ->assertSee('href="'.route('tutorial.show', 'sudah-tayang').'" target="_blank" rel="noopener"', false)
        ->assertDontSee('href="'.route('tutorial.show', 'tanpa-materi').'"', false)
        ->assertDontSee('href="'.route('tutorial.show', 'masih-draf').'"', false)
        ->assertSee('Belum tayang (belum ada materi)')
        ->assertSee('Belum tayang (draf)');
});

it('mencari tutorial berdasarkan judul atau deskripsi', function () {
    Tutorial::factory()->create(['judul' => 'Tutorial Flutter', 'deskripsi' => '<p>Aplikasi daftar tugas.</p>']);
    Tutorial::factory()->create(['judul' => 'Tutorial Laravel', 'deskripsi' => '<p>Aplikasi data buku.</p>']);

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index', ['q' => 'buku']))
        ->assertSee('Tutorial Laravel')
        ->assertDontSee('Tutorial Flutter');
});

it('menyaring tutorial berdasarkan kategori dan status, dengan pilihan kategori urut abjad', function () {
    $basisData = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);
    Tutorial::factory()->terbit()->for($basisData)->create(['judul' => 'MySQL terbit']);
    Tutorial::factory()->draf()->for($basisData)->create(['judul' => 'MySQL draf']);
    Tutorial::factory()->terbit()->for($this->kategori)->create(['judul' => 'Laravel terbit']);

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index', ['kategori' => $basisData->id_kategori_tutorial, 'status' => 'terbit']))
        ->assertSee('MySQL terbit')
        ->assertDontSee('MySQL draf')
        ->assertDontSee('Laravel terbit')
        ->assertSee('<option value="'.$basisData->id_kategori_tutorial.'" selected>Basis Data</option>', false)
        ->assertSeeInOrder(['Semua kategori', 'Basis Data', 'Pemrograman Web']);
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index'))
        ->assertSee('Belum ada tutorial')
        ->assertSee('<a href="'.route('admin.tutorial.create').'" class="btn btn-success tombol-tambah"', false);

    Tutorial::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.index', ['q' => 'tidak-ada']))
        ->assertSee('Tutorial tidak ditemukan');
});

it('mengisi form tambah dengan status draf, pilihan kategori urut abjad, dan editor deskripsi', function () {
    $basisData = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.create'))
        ->assertOk()
        ->assertSee('<option value="draf" selected>Draf</option>', false)
        ->assertSee('<select id="id_kategori_tutorial" name="id_kategori_tutorial"', false)
        ->assertSeeInOrder([
            '<option value="'.$basisData->id_kategori_tutorial.'" >Basis Data</option>',
            '<option value="'.$this->kategori->id_kategori_tutorial.'" >Pemrograman Web</option>',
        ], false)
        ->assertDontSee('Belum ada kategori.')
        ->assertSee('rows="5" data-editor-sumber', false)
        ->assertDontSee('data-judul-bagian', false)
        ->assertSee('/2.000 karakter', false)
        ->assertSee('dibuat otomatis dari judul dan tidak berubah lagi setelah tutorial terbit.');
});

it('mengarahkan ke menu Kategori Tutorial bila belum ada kategori', function () {
    $this->kategori->forceDelete();

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.create'))
        ->assertSee('Belum ada kategori. Tambahkan lebih dulu lewat menu Kategori Tutorial.');
});

it('menambah tutorial beserta slug dan sampulnya', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.tutorial.store'), dataTutorialValid([
            'deskripsi' => '<p>Isi<script>alert(1)</script></p><h2>Sub</h2>',
            'gambar' => UploadedFile::fake()->image('sampul.png', 2400, 1260),
        ]))
        ->assertRedirect(route('admin.tutorial.index'))
        ->assertSessionHas('success');

    $tutorial = Tutorial::sole();
    expect($tutorial->judul)->toBe('Tutorial Laravel 13')
        ->and($tutorial->slug)->toBe('tutorial-laravel-13')
        ->and($tutorial->id_kategori_tutorial)->toBe($this->kategori->id_kategori_tutorial)
        ->and($tutorial->status_publikasi)->toBe(StatusPublikasi::Draf)
        // Deskripsi tanpa sub-judul: h2 dilepas tagnya, skrip dibuang.
        ->and($tutorial->deskripsi)->toBe('<p>Isi</p>Sub')
        ->and($tutorial->gambar)->toMatch('#^company-profile/tutorial/[0-9a-f-]{36}\.webp$#')
        ->and(array_slice(getimagesizefromstring((string) Storage::disk('public')->get($tutorial->gambar)), 0, 2))->toBe([1600, 840]);
});

it('menampilkan form ubah berisi data tersimpan dan kebijakan slugnya', function () {
    $terbit = Tutorial::factory()->terbit()->kategori('Basis Data')->create(['judul' => 'Tutorial Terbit']);
    $draf = Tutorial::factory()->draf()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.edit', $terbit))
        ->assertOk()
        ->assertSee('value="Tutorial Terbit"', false)
        ->assertSee('<option value="'.$terbit->id_kategori_tutorial.'" selected>Basis Data</option>', false)
        ->assertSee('<option value="terbit" selected>Terbit</option>', false)
        ->assertSee(Storage::disk('public')->url($terbit->gambar))
        ->assertSee('Alamat halaman tetap meskipun judul diubah');

    $this->actingAs($this->admin)
        ->get(route('admin.tutorial.edit', $draf))
        ->assertSee('Alamat halaman ikut berubah bila judul diubah selama tutorial masih draf.');
});

it('memperbarui tutorial tanpa mengganti sampul', function () {
    $tutorial = Tutorial::factory()->draf()->for($this->kategori)->create();
    $gambarLama = $tutorial->gambar;
    $basisData = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);

    $this->actingAs($this->admin)
        ->put(route('admin.tutorial.update', $tutorial), dataTutorialValid([
            'id_kategori_tutorial' => $basisData->id_kategori_tutorial, 'gambar' => null, 'status_publikasi' => 'terbit',
        ]))
        ->assertRedirect(route('admin.tutorial.index'))
        ->assertSessionHasNoErrors();

    expect($tutorial->refresh()->id_kategori_tutorial)->toBe($basisData->id_kategori_tutorial)
        ->and($tutorial->status_publikasi)->toBe(StatusPublikasi::Terbit)
        ->and($tutorial->gambar)->toBe($gambarLama);
});

it('memvalidasi isian tutorial', function (Closure $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.tutorial.create'))
        ->post(route('admin.tutorial.store'), dataTutorialValid($data()))
        ->assertRedirect(route('admin.tutorial.create'))
        ->assertSessionHasErrors($kolom);

    expect(Tutorial::count())->toBe(0);
})->with([
    'kategori kosong' => [fn () => ['id_kategori_tutorial' => ''], 'id_kategori_tutorial'],
    'kategori tidak ada' => [fn () => ['id_kategori_tutorial' => 999999], 'id_kategori_tutorial'],
    'kategori sudah dihapus' => [fn () => ['id_kategori_tutorial' => tap(KategoriTutorial::factory()->create())->delete()->id_kategori_tutorial], 'id_kategori_tutorial'],
    'judul kosong' => [fn () => ['judul' => ''], 'judul'],
    'judul terlalu panjang' => [fn () => ['judul' => str_repeat('a', 201)], 'judul'],
    'deskripsi kosong' => [fn () => ['deskripsi' => '<p></p>'], 'deskripsi'],
    'deskripsi lebih dari 2.000 karakter terlihat' => [fn () => ['deskripsi' => '<p>'.str_repeat('a', 2001).'</p>'], 'deskripsi'],
    'sampul kosong saat tambah' => [fn () => ['gambar' => null], 'gambar'],
    'sampul bukan gambar' => [fn () => ['gambar' => UploadedFile::fake()->create('sampul.pdf', 10, 'application/pdf')], 'gambar'],
    'status tidak dikenal' => [fn () => ['status_publikasi' => 'arsip'], 'status_publikasi'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.tutorial.store'), dataTutorialValid(['id_kategori_tutorial' => '', 'gambar' => null]))
        ->assertSessionHasErrors([
            'id_kategori_tutorial' => 'Kolom kategori wajib diisi.',
            'gambar' => 'Kolom gambar sampul wajib diisi.',
        ]);
});

it('menghapus tutorial beserta materinya secara soft delete tanpa menyentuh tutorial lain', function () {
    Storage::disk('public')->put('company-profile/tutorial/tetap.webp', 'isi');
    $tutorial = Tutorial::factory()->create(['gambar' => 'company-profile/tutorial/tetap.webp']);
    $materi = MateriTutorial::factory()->count(2)->for($tutorial)->create();
    $materiLain = MateriTutorial::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.tutorial.destroy', $tutorial))
        ->assertRedirect(route('admin.tutorial.index'))
        ->assertSessionHas('success', 'Tutorial beserta materinya berhasil dihapus.');

    $this->assertSoftDeleted($tutorial);
    $materi->each(fn (MateriTutorial $item) => $this->assertSoftDeleted($item));
    $this->assertNotSoftDeleted($materiLain);
    Storage::disk('public')->assertExists('company-profile/tutorial/tetap.webp');
});

it('membuat tutorial contoh dari seeder dummy tanpa menggandakan saat dijalankan ulang', function () {
    $this->kategori->forceDelete();
    $this->seed([KategoriTutorialSeeder::class, TutorialDummySeeder::class]);
    $this->seed([KategoriTutorialSeeder::class, TutorialDummySeeder::class]);

    $semua = Tutorial::with(['kategoriTutorial', 'materiTutorial'])->get();
    expect(KategoriTutorial::query()->pluck('nama_kategori')->all())->toBe(KategoriTutorialSeeder::NAMA)
        // Dua tutorial per kategori, semuanya terbit dengan empat materi.
        ->and($semua->countBy(fn (Tutorial $item) => $item->kategoriTutorial->nama_kategori)->sortKeys()->all())
        ->toBe(array_fill_keys(KategoriTutorialSeeder::NAMA, 2))
        ->and($semua->every(fn (Tutorial $item) => $item->status_publikasi === StatusPublikasi::Terbit
            && $item->materiTutorial->pluck('urutan_ke')->all() === range(1, 4)))->toBeTrue()
        // Tutorial dasar setiap kategori tampil lebih dulu di daftar publik.
        ->and(Tutorial::query()->terbaru()->limit(5)->pluck('judul')->all())->toBe([
            'Dasar-Dasar Laravel 13', 'Dasar-Dasar JavaScript Modern', 'Dasar-Dasar TypeScript',
            'Pengantar Machine Learning dengan Python', 'Pengantar Deep Learning dengan Keras',
        ]);

    $tutorial = $semua->firstWhere('judul', 'Dasar-Dasar Laravel 13');
    expect($tutorial->kategoriTutorial->nama_kategori)->toBe('Laravel')
        ->and($tutorial->materiTutorial->first()->judul)->toBe('Fitur-Fitur Baru di Laravel 13')
        // Sampul memakai ulang ilustrasi Laravel artikel.
        ->and(Storage::disk('public')->get($tutorial->gambar))->toBe(file_get_contents(database_path('seeders/berkas/artikel/laravel.webp')))
        // Baris baru & indentasi di dalam blok kode dipertahankan.
        ->and($tutorial->materiTutorial->firstWhere('urutan_ke', 4)->deskripsi)
        ->toContain("<pre><code>public function up(): void\n{\n    Schema::create(&#039;buku&#039;");
});

it('menulis isi materi seeder hanya dengan format yang didukung editor', function () {
    $sanitizer = app(HtmlSanitizerService::class);
    $tag = fn (string $html): array => preg_match_all('#</?[a-z0-9]+#', $html, $cocok) ? $cocok[0] : [];
    $semuaBerkas = glob(database_path('seeders/data/tutorial/*/*.html'));

    expect($semuaBerkas)->toHaveCount(40);

    foreach ($semuaBerkas as $berkas) {
        $html = (string) file_get_contents($berkas);
        $bersih = (string) $sanitizer->bersihkan($html, judulBagian: true, kode: true);

        // Sanitizer hanya menyeragamkan entitas; tidak ada tag maupun teks yang dibuang.
        expect($tag($bersih))->toBe($tag($html), basename($berkas))
            ->and(TeksHtml::polos($bersih))->toBe(TeksHtml::polos($html));
    }
});
