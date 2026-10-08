<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\Pengguna;
use Database\Seeders\KategoriArtikelSeeder;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

it('mengarahkan tamu ke halaman login', function () {
    $kategori = KategoriArtikel::factory()->create();

    $this->get(route('admin.kategori-artikel.index'))->assertRedirect(route('login'));
    $this->get(route('admin.kategori-artikel.create'))->assertRedirect(route('login'));
    $this->post(route('admin.kategori-artikel.store'), ['nama_kategori' => 'Teknologi'])->assertRedirect(route('login'));
    $this->get(route('admin.kategori-artikel.edit', $kategori))->assertRedirect(route('login'));
    $this->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Teknologi'])->assertRedirect(route('login'));
    $this->delete(route('admin.kategori-artikel.destroy', $kategori))->assertRedirect(route('login'));
});

it('menampilkan daftar kategori urut abjad', function () {
    KategoriArtikel::factory()->create(['nama_kategori' => 'Tips & Tutorial']);
    KategoriArtikel::factory()->create(['nama_kategori' => 'Kabar Perusahaan']);
    KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index'))
        ->assertOk()
        ->assertSeeInOrder(['Kabar Perusahaan', 'Teknologi', 'Tips &amp; Tutorial'], false)
        ->assertSee('class="nav-sublink active" href="'.route('admin.kategori-artikel.index').'"', false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    $kategori = KategoriArtikel::factory()->create(['created_at' => '2026-10-09 08:30:00']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Kategori Artikel</div>',
            'href="'.route('admin.kategori-artikel.create').'"',
            '<table class="table">',
            'data-detail-title="Detail Kategori Artikel"',
            '09 Oktober 2026, 08:30',
            'href="'.route('admin.kategori-artikel.edit', $kategori).'"',
            'action="'.route('admin.kategori-artikel.destroy', $kategori).'"',
        ], false);
});

it('mencari kategori berdasarkan nama', function () {
    KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
    KategoriArtikel::factory()->create(['nama_kategori' => 'Kabar Perusahaan']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index', ['q' => 'tekno']))
        ->assertSee('Teknologi')
        ->assertDontSee('Kabar Perusahaan');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index'))
        ->assertSee('Belum ada kategori artikel')
        ->assertSee('Tambah Kategori Pertama');

    KategoriArtikel::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index', ['q' => 'tidak-ada']))
        ->assertSee('Kategori artikel tidak ditemukan');
});

it('menambah kategori artikel', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.create'))
        ->assertOk()
        ->assertSee('name="nama_kategori"', false);

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-artikel.store'), ['nama_kategori' => 'Teknologi'])
        ->assertRedirect(route('admin.kategori-artikel.index'))
        ->assertSessionHas('success');

    expect(KategoriArtikel::sole()->nama_kategori)->toBe('Teknologi');
});

it('boleh memakai ulang nama kategori yang sudah dihapus', function () {
    KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-artikel.store'), ['nama_kategori' => 'Teknologi'])
        ->assertSessionHasNoErrors();
});

it('memperbarui kategori artikel dengan nama sendiri', function () {
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.edit', $kategori))
        ->assertOk()
        ->assertSee('value="Teknologi"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Teknologi'])
        ->assertRedirect(route('admin.kategori-artikel.index'))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Teknologi Informasi'])
        ->assertSessionHasNoErrors();

    expect($kategori->refresh()->nama_kategori)->toBe('Teknologi Informasi');
});

it('menolak nama yang sudah dipakai kategori lain saat mengubah', function () {
    KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Tips & Tutorial']);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-artikel.update', $kategori), ['nama_kategori' => 'Teknologi'])
        ->assertSessionHasErrors(['nama_kategori' => 'Nama kategori sudah dipakai kategori lain.']);
});

it('memvalidasi nama kategori', function (mixed $nama, string $pesan) {
    KategoriArtikel::factory()->create(['nama_kategori' => 'Sudah Ada']);

    $this->actingAs($this->admin)
        ->from(route('admin.kategori-artikel.create'))
        ->post(route('admin.kategori-artikel.store'), ['nama_kategori' => $nama])
        ->assertRedirect(route('admin.kategori-artikel.create'))
        ->assertSessionHasErrors(['nama_kategori' => $pesan]);

    expect(KategoriArtikel::count())->toBe(1);
})->with([
    'kosong' => ['', 'Kolom nama kategori wajib diisi.'],
    'terlalu panjang' => [str_repeat('a', 101), 'Kolom nama kategori tidak boleh lebih dari 100 karakter.'],
    'sudah dipakai' => ['Sudah Ada', 'Nama kategori sudah dipakai kategori lain.'],
]);

it('menghapus kategori artikel secara soft delete', function () {
    $kategori = KategoriArtikel::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-artikel.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-artikel.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('menampilkan jumlah artikel per kategori tanpa menghitung artikel terhapus', function () {
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
    Artikel::factory()->count(2)->for($kategori)->create();
    Artikel::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-artikel.index'))
        ->assertSeeInOrder(['Teknologi', '<td style="text-align:center">2</td>', 'Jumlah Artikel</th>', '<td>2</td>'], false)
        ->assertSee('masih dipakai 2 artikel sehingga tidak bisa dihapus.', false);
});

it('menolak menghapus kategori yang masih dipakai artikel', function () {
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
    Artikel::factory()->for($kategori)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-artikel.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-artikel.index'))
        ->assertSessionHas('error', 'Kategori “Teknologi” masih dipakai artikel. Pindahkan atau hapus artikelnya lebih dulu.');

    expect($kategori->refresh()->trashed())->toBeFalse();
});

it('boleh menghapus kategori yang artikelnya sudah dihapus semua', function () {
    $kategori = KategoriArtikel::factory()->create();
    Artikel::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-artikel.destroy', $kategori))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('membuat empat kategori awal dari seeder tanpa menimpa isian admin', function () {
    $this->seed(KategoriArtikelSeeder::class);
    KategoriArtikel::query()->where('nama_kategori', 'Teknologi')->update(['nama_kategori' => 'Teknologi Informasi']);

    $this->seed(KategoriArtikelSeeder::class);

    expect(KategoriArtikel::orderBy('nama_kategori')->pluck('nama_kategori')->all())
        ->toBe(['Kabar Perusahaan', 'Pelatihan & Sertifikasi', 'Teknologi Informasi', 'Tips & Tutorial']);
});
