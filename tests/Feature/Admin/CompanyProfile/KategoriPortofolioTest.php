<?php

use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use Database\Seeders\KategoriPortofolioSeeder;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

it('mengarahkan tamu ke halaman login', function () {
    $kategori = KategoriPortofolio::factory()->create();

    $this->get(route('admin.kategori-portofolio.index'))->assertRedirect(route('login'));
    $this->get(route('admin.kategori-portofolio.create'))->assertRedirect(route('login'));
    $this->post(route('admin.kategori-portofolio.store'), ['nama_kategori' => 'Website'])->assertRedirect(route('login'));
    $this->get(route('admin.kategori-portofolio.edit', $kategori))->assertRedirect(route('login'));
    $this->put(route('admin.kategori-portofolio.update', $kategori), ['nama_kategori' => 'Website'])->assertRedirect(route('login'));
    $this->delete(route('admin.kategori-portofolio.destroy', $kategori))->assertRedirect(route('login'));
});

it('menampilkan daftar kategori urut abjad di submenu Portofolio', function () {
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Bootcamp']);
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Mobile Apps']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index'))
        ->assertOk()
        ->assertSeeInOrder(['Bootcamp', 'Mobile Apps', 'Website'])
        ->assertSeeInOrder([
            'href="'.route('admin.portofolio.index').'"',
            'class="nav-sublink active" href="'.route('admin.kategori-portofolio.index').'"',
        ], false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    $kategori = KategoriPortofolio::factory()->create(['created_at' => '2026-10-18 08:30:00']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Kategori Portofolio</div>',
            'href="'.route('admin.kategori-portofolio.create').'"',
            '<table class="table">',
            'data-detail-title="Detail Kategori Portofolio"',
            '18 Oktober 2026, 08:30',
            'href="'.route('admin.kategori-portofolio.edit', $kategori).'"',
            'action="'.route('admin.kategori-portofolio.destroy', $kategori).'"',
        ], false);
});

it('mencari kategori berdasarkan nama', function () {
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Mobile Apps']);
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Bootcamp']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index', ['q' => 'mobile']))
        ->assertSee('Mobile Apps')
        ->assertDontSee('Bootcamp');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index'))
        ->assertSee('Belum ada kategori portofolio')
        ->assertSee('<a href="'.route('admin.kategori-portofolio.create').'" class="btn btn-success tombol-tambah"', false);

    KategoriPortofolio::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index', ['q' => 'tidak-ada']))
        ->assertSee('Kategori portofolio tidak ditemukan');
});

it('menambah kategori portofolio', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.create'))
        ->assertOk()
        ->assertSee('name="nama_kategori"', false);

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-portofolio.store'), ['nama_kategori' => 'Pelatihan IT'])
        ->assertRedirect(route('admin.kategori-portofolio.index'))
        ->assertSessionHas('success');

    expect(KategoriPortofolio::sole()->nama_kategori)->toBe('Pelatihan IT');
});

it('memperbarui kategori portofolio dengan nama sendiri maupun nama baru', function () {
    $kategori = KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.edit', $kategori))
        ->assertOk()
        ->assertSee('value="Website"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-portofolio.update', $kategori), ['nama_kategori' => 'Website'])
        ->assertRedirect(route('admin.kategori-portofolio.index'))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-portofolio.update', $kategori), ['nama_kategori' => 'Website & Sistem Informasi'])
        ->assertSessionHasNoErrors();

    expect($kategori->refresh()->nama_kategori)->toBe('Website & Sistem Informasi');
});

it('menolak nama yang sudah dipakai kategori lain saat mengubah', function () {
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);
    $kategori = KategoriPortofolio::factory()->create(['nama_kategori' => 'Bootcamp']);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-portofolio.update', $kategori), ['nama_kategori' => 'Website'])
        ->assertSessionHasErrors(['nama_kategori' => 'Nama kategori sudah dipakai kategori lain.']);
});

it('boleh memakai ulang nama kategori yang sudah dihapus', function () {
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Bootcamp'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-portofolio.store'), ['nama_kategori' => 'Bootcamp'])
        ->assertSessionHasNoErrors();

    expect(KategoriPortofolio::sole()->nama_kategori)->toBe('Bootcamp');
});

it('memvalidasi nama kategori', function (mixed $nama, string $pesan) {
    KategoriPortofolio::factory()->create(['nama_kategori' => 'Sudah Ada']);

    $this->actingAs($this->admin)
        ->from(route('admin.kategori-portofolio.create'))
        ->post(route('admin.kategori-portofolio.store'), ['nama_kategori' => $nama])
        ->assertRedirect(route('admin.kategori-portofolio.create'))
        ->assertSessionHasErrors(['nama_kategori' => $pesan]);

    expect(KategoriPortofolio::count())->toBe(1);
})->with([
    'kosong' => ['', 'Kolom nama kategori wajib diisi.'],
    'terlalu panjang' => [str_repeat('a', 101), 'Kolom nama kategori tidak boleh lebih dari 100 karakter.'],
    'sudah dipakai' => ['Sudah Ada', 'Nama kategori sudah dipakai kategori lain.'],
]);

it('menghapus kategori portofolio secara soft delete', function () {
    $kategori = KategoriPortofolio::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-portofolio.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-portofolio.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('menampilkan jumlah portofolio per kategori tanpa menghitung portofolio terhapus', function () {
    $kategori = KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);
    Portofolio::factory()->count(2)->for($kategori)->create();
    Portofolio::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-portofolio.index'))
        ->assertSeeInOrder(['Website', '<td class="kolom-jumlah">2</td>', 'Jumlah Portofolio</th>', '<td>2</td>'], false)
        ->assertSee('masih dipakai 2 portofolio sehingga tidak bisa dihapus.', false);
});

it('menolak menghapus kategori yang masih dipakai portofolio', function () {
    $kategori = KategoriPortofolio::factory()->create(['nama_kategori' => 'Website']);
    Portofolio::factory()->for($kategori)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-portofolio.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-portofolio.index'))
        ->assertSessionHas('error', 'Kategori “Website” masih dipakai portofolio. Pindahkan atau hapus portofolionya lebih dulu.');

    expect($kategori->refresh()->trashed())->toBeFalse();
});

it('boleh menghapus kategori yang portofolionya sudah dihapus semua', function () {
    $kategori = KategoriPortofolio::factory()->create();
    Portofolio::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-portofolio.destroy', $kategori))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('membuat lima kategori awal berurutan dari seeder tanpa menimpa isian admin', function () {
    $this->seed(KategoriPortofolioSeeder::class);

    // Urutan dibuat = urutan kartu portofolio di beranda.
    expect(KategoriPortofolio::orderBy('id_kategori_portofolio')->pluck('nama_kategori')->all())
        ->toBe(['Website', 'Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp']);

    KategoriPortofolio::query()->where('nama_kategori', 'Bootcamp')->update(['nama_kategori' => 'Bootcamp Mahasiswa']);

    $this->seed(KategoriPortofolioSeeder::class);

    expect(KategoriPortofolio::count())->toBe(5)
        ->and(KategoriPortofolio::where('nama_kategori', 'Bootcamp Mahasiswa')->exists())->toBeTrue()
        ->and(KategoriPortofolio::where('nama_kategori', 'Bootcamp')->exists())->toBeFalse();
});
