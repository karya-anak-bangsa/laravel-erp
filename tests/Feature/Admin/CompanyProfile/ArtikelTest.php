<?php

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\Pengguna;
use App\Services\Shared\HtmlSanitizerService;
use App\Support\TeksHtml;
use Database\Seeders\ArtikelDummySeeder;
use Database\Seeders\KategoriArtikelSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
    $this->kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
});

function dataArtikelValid(array $timpa = []): array
{
    return [
        'id_kategori_artikel' => KategoriArtikel::query()->value('id_kategori_artikel'),
        'judul' => '5 Alasan Bisnis Kecil Perlu Website',
        'deskripsi' => '<h2>Dipercaya pelanggan</h2><p>Website membuat bisnis lebih meyakinkan.</p>',
        'gambar' => UploadedFile::fake()->image('artikel.webp', 1200, 630),
        'tanggal' => '2026-10-09',
        'status_publikasi' => 'terbit',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $artikel = Artikel::factory()->create();

    $this->get(route('admin.artikel.index'))->assertRedirect(route('login'));
    $this->get(route('admin.artikel.create'))->assertRedirect(route('login'));
    $this->post(route('admin.artikel.store'), dataArtikelValid())->assertRedirect(route('login'));
    $this->get(route('admin.artikel.edit', $artikel))->assertRedirect(route('login'));
    $this->put(route('admin.artikel.update', $artikel), dataArtikelValid())->assertRedirect(route('login'));
    $this->delete(route('admin.artikel.destroy', $artikel))->assertRedirect(route('login'));
});

it('menampilkan artikel terbaru lebih dulu beserta kategori dan status', function () {
    Artikel::factory()->for($this->kategori)->terbit()->create(['judul' => 'Artikel lama', 'tanggal' => '2026-09-01']);
    Artikel::factory()->for($this->kategori)->draf()->create(['judul' => 'Artikel terbaru', 'tanggal' => '2026-10-09']);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Artikel terbaru', 'Teknologi', '09 Oktober 2026', 'status-yellow">Draf',
            'Artikel lama', 'Teknologi', '01 September 2026', 'status-green">Terbit',
        ], false)
        ->assertSee('class="nav-sublink active" href="'.route('admin.artikel.index').'"', false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin dengan rincian untuk modal', function () {
    $artikel = Artikel::factory()->for($this->kategori)->create([
        'slug' => 'tips-website',
        'deskripsi' => '<h2>Bagian satu</h2><p>Isi <strong>tebal</strong></p>',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSee('<div class="card-subtitle">Bagian satu Isi tebal</div>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Daftar Artikel</div>',
            'href="'.route('admin.artikel.create').'"',
            'data-detail-title="Detail Artikel"',
            '/artikel/tips-website',
            '<td class="konten-html"><h2>Bagian satu</h2><p>Isi <strong>tebal</strong></p></td>',
            Storage::disk('public')->url($artikel->gambar),
            'href="'.route('admin.artikel.edit', $artikel).'"',
            'action="'.route('admin.artikel.destroy', $artikel).'"',
        ], false);
});

it('mencari artikel berdasarkan judul atau isi', function () {
    Artikel::factory()->for($this->kategori)->create(['judul' => 'Keamanan Website', 'deskripsi' => '<p>Gunakan HTTPS.</p>']);
    Artikel::factory()->for($this->kategori)->create(['judul' => 'Aplikasi Mobile', 'deskripsi' => '<p>Native atau hybrid.</p>']);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index', ['q' => 'hybrid']))
        ->assertSee('Aplikasi Mobile')
        ->assertDontSee('Keamanan Website');
});

it('menyaring artikel berdasarkan kategori dan status', function () {
    $lain = KategoriArtikel::factory()->create(['nama_kategori' => 'Kabar Perusahaan']);
    Artikel::factory()->for($this->kategori)->terbit()->create(['judul' => 'Teknologi terbit']);
    Artikel::factory()->for($this->kategori)->draf()->create(['judul' => 'Teknologi draf']);
    Artikel::factory()->for($lain)->terbit()->create(['judul' => 'Kabar terbit']);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index', ['kategori' => $this->kategori->id_kategori_artikel, 'status' => 'terbit']))
        ->assertSee('Teknologi terbit')
        ->assertDontSee('Teknologi draf')
        ->assertDontSee('Kabar terbit')
        ->assertSee('<option value="'.$this->kategori->id_kategori_artikel.'" selected>Teknologi</option>', false);
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index'))
        ->assertSee('Belum ada artikel')
        ->assertDontSee('Tambah Artikel Pertama')
        ->assertSee('<a href="'.route('admin.artikel.create').'" class="btn btn-success tombol-tambah"', false);

    Artikel::factory()->for($this->kategori)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index', ['status' => 'draf', 'q' => 'tidak-ada']))
        ->assertSee('Artikel tidak ditemukan');
});

it('mengisi form tambah dengan tanggal hari ini, status draf, dan editor isi artikel', function () {
    $this->travelTo('2026-10-09 10:00:00');

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.create'))
        ->assertOk()
        ->assertSee('type="date"', false)
        ->assertSee('value="2026-10-09"', false)
        ->assertSee('<option value="draf" selected>Draf</option>', false)
        ->assertSee('<option value="'.$this->kategori->id_kategori_artikel.'" >Teknologi</option>', false)
        // Isi artikel: sub-judul H2/H3, pas layar, dan tombol Layar penuh (pilihan pemilik).
        ->assertSee('class="editor editor-pas-layar" data-editor data-judul-bagian>', false)
        ->assertSee('data-layar-penuh', false)
        ->assertSee('<span class="current">Isi Artikel</span>', false)
        ->assertSee('rows="8" data-editor-sumber', false)
        ->assertSeeInOrder(['data-perintah="judul2"', 'H2', 'data-perintah="judul3"', 'H3', 'data-perintah="bold"'], false)
        ->assertSee('/30.000 karakter', false);
});

it('menambah artikel beserta slug, gambar, dan sub-judul isinya', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.artikel.store'), dataArtikelValid([
            'deskripsi' => '<h1>Judul besar</h1><h2 style="text-align: center">Bagian</h2><h3>Sub</h3><h4>Kecil</h4><p>Isi<script>alert(1)</script></p>',
            'gambar' => UploadedFile::fake()->image('artikel.png', 2400, 1260),
        ]))
        ->assertRedirect(route('admin.artikel.index'))
        ->assertSessionHas('success');

    $artikel = Artikel::sole();
    expect($artikel->judul)->toBe('5 Alasan Bisnis Kecil Perlu Website')
        ->and($artikel->slug)->toBe('5-alasan-bisnis-kecil-perlu-website')
        ->and($artikel->kategoriArtikel->is($this->kategori))->toBeTrue()
        ->and($artikel->tanggal->format('Y-m-d'))->toBe('2026-10-09')
        ->and($artikel->status_publikasi)->toBe(StatusPublikasi::Terbit)
        // h2/h3 (beserta perataannya) dipertahankan; h1/h4 dilepas tagnya; skrip dibuang.
        ->and($artikel->deskripsi)->toBe('Judul besar<h2 style="text-align: center">Bagian</h2><h3>Sub</h3>Kecil<p>Isi</p>')
        // Gambar konten diperkecil menjadi WebP bersisi terpanjang 1600 px.
        ->and($artikel->gambar)->toMatch('#^company-profile/artikel/[0-9a-f-]{36}\.webp$#')
        ->and(array_slice(getimagesizefromstring((string) Storage::disk('public')->get($artikel->gambar)), 0, 2))->toBe([1600, 840]);
});

it('menghitung batas isi artikel dari teks terlihat hingga 30.000 karakter', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.artikel.store'), dataArtikelValid(['deskripsi' => '<h2>Bagian</h2><p>'.str_repeat('a', 30000 - 7).'</p>']))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->post(route('admin.artikel.store'), dataArtikelValid(['deskripsi' => '<p>'.str_repeat('a', 30001).'</p>']))
        ->assertSessionHasErrors(['deskripsi' => 'Kolom isi artikel tidak boleh lebih dari 30000 karakter.']);
});

it('menampilkan form ubah berisi data tersimpan', function () {
    $artikel = Artikel::factory()->for($this->kategori)->terbit()->create(['judul' => 'Keamanan Website', 'slug' => 'keamanan-website', 'tanggal' => '2026-09-15']);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.edit', $artikel))
        ->assertOk()
        ->assertSee('value="Keamanan Website"', false)
        ->assertSee('/artikel/keamanan-website')
        ->assertSee('value="2026-09-15"', false)
        ->assertSee('<option value="terbit" selected>Terbit</option>', false)
        ->assertSee(Storage::disk('public')->url($artikel->gambar));
});

it('menjelaskan kebijakan slug di form sesuai status artikel', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.artikel.create'))
        ->assertSee('dibuat otomatis dari judul dan tidak berubah lagi setelah artikel terbit.');

    $draf = Artikel::factory()->for($this->kategori)->draf()->create(['slug' => 'artikel-draf']);
    $this->actingAs($this->admin)
        ->get(route('admin.artikel.edit', $draf))
        ->assertSee('/artikel/artikel-draf — ikut berubah bila judul diubah selama artikel masih draf.');

    $terbit = Artikel::factory()->for($this->kategori)->terbit()->create(['slug' => 'artikel-terbit']);
    $this->actingAs($this->admin)
        ->get(route('admin.artikel.edit', $terbit))
        ->assertSee('/artikel/artikel-terbit — tetap meskipun judul diubah, agar tautan yang sudah dibagikan tidak rusak.');
});

it('menyediakan tombol Buka halaman di modal detail hanya untuk artikel terbit', function () {
    Artikel::factory()->for($this->kategori)->terbit()->create(['slug' => 'sudah-tayang']);
    Artikel::factory()->for($this->kategori)->draf()->create(['slug' => 'belum-tayang']);

    $this->actingAs($this->admin)
        ->get(route('admin.artikel.index'))
        ->assertSee('href="'.route('artikel.show', 'sudah-tayang').'" target="_blank" rel="noopener"', false)
        ->assertDontSee('href="'.route('artikel.show', 'belum-tayang').'"', false)
        ->assertSee('Belum tayang (draf)');
});

it('memperbarui artikel tanpa mengganti gambar', function () {
    $lain = KategoriArtikel::factory()->create();
    $artikel = Artikel::factory()->for($this->kategori)->terbit()->create();
    $gambarLama = $artikel->gambar;

    $this->actingAs($this->admin)
        ->put(route('admin.artikel.update', $artikel), dataArtikelValid([
            'id_kategori_artikel' => $lain->id_kategori_artikel, 'gambar' => null, 'status_publikasi' => 'draf',
        ]))
        ->assertRedirect(route('admin.artikel.index'))
        ->assertSessionHasNoErrors();

    expect($artikel->refresh()->id_kategori_artikel)->toBe($lain->id_kategori_artikel)
        ->and($artikel->status_publikasi)->toBe(StatusPublikasi::Draf)
        ->and($artikel->gambar)->toBe($gambarLama);
});

it('memvalidasi isian artikel', function (Closure $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.artikel.create'))
        ->post(route('admin.artikel.store'), dataArtikelValid($data()))
        ->assertRedirect(route('admin.artikel.create'))
        ->assertSessionHasErrors($kolom);

    expect(Artikel::count())->toBe(0);
})->with([
    'kategori kosong' => [fn () => ['id_kategori_artikel' => ''], 'id_kategori_artikel'],
    'kategori tidak ada' => [fn () => ['id_kategori_artikel' => 999999], 'id_kategori_artikel'],
    'kategori sudah dihapus' => [fn () => ['id_kategori_artikel' => tap(KategoriArtikel::factory()->create())->delete()->id_kategori_artikel], 'id_kategori_artikel'],
    'judul kosong' => [fn () => ['judul' => ''], 'judul'],
    'judul terlalu panjang' => [fn () => ['judul' => str_repeat('a', 201)], 'judul'],
    'isi kosong' => [fn () => ['deskripsi' => '<p></p>'], 'deskripsi'],
    'gambar kosong saat tambah' => [fn () => ['gambar' => null], 'gambar'],
    'gambar bukan gambar' => [fn () => ['gambar' => UploadedFile::fake()->create('artikel.pdf', 10, 'application/pdf')], 'gambar'],
    'gambar lebih dari 2 MB' => [fn () => ['gambar' => UploadedFile::fake()->image('artikel.png')->size(2049)], 'gambar'],
    'tanggal kosong' => [fn () => ['tanggal' => ''], 'tanggal'],
    'tanggal salah format' => [fn () => ['tanggal' => '09/10/2026'], 'tanggal'],
    'status kosong' => [fn () => ['status_publikasi' => ''], 'status_publikasi'],
    'status tidak dikenal' => [fn () => ['status_publikasi' => 'arsip'], 'status_publikasi'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.artikel.store'), dataArtikelValid(['id_kategori_artikel' => '', 'deskripsi' => '']))
        ->assertSessionHasErrors([
            'id_kategori_artikel' => 'Kolom kategori wajib diisi.',
            'deskripsi' => 'Kolom isi artikel wajib diisi.',
        ]);
});

it('menghapus artikel secara soft delete tanpa menghapus gambarnya', function () {
    Storage::disk('public')->put('company-profile/artikel/tetap.webp', 'isi');
    $artikel = Artikel::factory()->for($this->kategori)->create(['gambar' => 'company-profile/artikel/tetap.webp']);

    $this->actingAs($this->admin)
        ->delete(route('admin.artikel.destroy', $artikel))
        ->assertRedirect(route('admin.artikel.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($artikel);
    Storage::disk('public')->assertExists('company-profile/artikel/tetap.webp');
});

it('membuat artikel contoh dari seeder dummy tanpa menggandakan saat dijalankan ulang', function () {
    $this->seed(KategoriArtikelSeeder::class);
    $this->seed(ArtikelDummySeeder::class);
    $this->seed(ArtikelDummySeeder::class);

    expect(Artikel::count())->toBe(32)
        ->and(Artikel::where('status_publikasi', 'draf')->count())->toBe(2)
        // Tiga artikel terbit di setiap kategori awal (kategori Teknologi dari beforeEach tidak dihitung).
        ->and(KategoriArtikel::whereIn('nama_kategori', KategoriArtikelSeeder::NAMA)->withCount('artikelTerbit')
            ->pluck('artikel_terbit_count')->all())->toBe(array_fill(0, count(KategoriArtikelSeeder::NAMA), 3))
        ->and(Artikel::where('judul', 'Windows 11 26H2 Resmi Masuk Release Preview, Begini Cara Mencobanya')->value('deskripsi'))
        ->toStartWith('<p>')->toContain('<h2>');

    // Setiap artikel mendapat salinan ilustrasi kategorinya sendiri.
    Artikel::with('kategoriArtikel')->get()->each(function (Artikel $artikel) {
        expect(Storage::disk('public')->get($artikel->gambar))
            ->toBe(file_get_contents(database_path("seeders/berkas/artikel/{$artikel->kategoriArtikel->slug}.webp")));
    });
});

it('menulis isi artikel seeder hanya dengan format yang didukung editor', function () {
    $sanitizer = app(HtmlSanitizerService::class);
    $tag = fn (string $html): array => preg_match_all('#</?[a-z0-9]+#', $html, $cocok) ? $cocok[0] : [];
    $semuaBerkas = glob(database_path('seeders/data/artikel/*.html'));

    expect($semuaBerkas)->toHaveCount(32);

    foreach ($semuaBerkas as $berkas) {
        $html = implode('', array_map('trim', file($berkas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
        $bersih = (string) $sanitizer->bersihkan($html, judulBagian: true);

        // Sanitizer hanya menyeragamkan entitas; tidak ada tag maupun teks yang dibuang.
        expect($tag($bersih))->toBe($tag($html), basename($berkas))
            ->and(TeksHtml::polos($bersih))->toBe(TeksHtml::polos($html));
    }
});
