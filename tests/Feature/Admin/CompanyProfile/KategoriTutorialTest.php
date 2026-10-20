<?php

use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;
use Database\Seeders\KategoriTutorialSeeder;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

it('mengarahkan tamu ke halaman login', function () {
    $kategori = KategoriTutorial::factory()->create();

    $this->get(route('admin.kategori-tutorial.index'))->assertRedirect(route('login'));
    $this->get(route('admin.kategori-tutorial.create'))->assertRedirect(route('login'));
    $this->post(route('admin.kategori-tutorial.store'), ['nama_kategori' => 'Pemrograman Web'])->assertRedirect(route('login'));
    $this->get(route('admin.kategori-tutorial.edit', $kategori))->assertRedirect(route('login'));
    $this->put(route('admin.kategori-tutorial.update', $kategori), ['nama_kategori' => 'Pemrograman Web'])->assertRedirect(route('login'));
    $this->delete(route('admin.kategori-tutorial.destroy', $kategori))->assertRedirect(route('login'));
});

it('menampilkan daftar kategori urut abjad di submenu Tutorial setelah Materi Tutorial', function () {
    KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);
    KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);
    KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Mobile']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index'))
        ->assertOk()
        ->assertSeeInOrder(['Basis Data', 'Pemrograman Mobile', 'Pemrograman Web'])
        ->assertSeeInOrder([
            'href="'.route('admin.tutorial.index').'"',
            'href="'.route('admin.materi-tutorial.index').'"',
            'class="nav-sublink active" href="'.route('admin.kategori-tutorial.index').'"',
        ], false)
        ->assertSee('class="nav-tree open has-active"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    $kategori = KategoriTutorial::factory()->create(['created_at' => '2026-10-18 08:30:00']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Kategori Tutorial</div>',
            'href="'.route('admin.kategori-tutorial.create').'"',
            '<table class="table">',
            'data-detail-title="Detail Kategori Tutorial"',
            '18 Oktober 2026, 08:30',
            'href="'.route('admin.kategori-tutorial.edit', $kategori).'"',
            'action="'.route('admin.kategori-tutorial.destroy', $kategori).'"',
        ], false);
});

it('mencari kategori berdasarkan nama', function () {
    KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Mobile']);
    KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index', ['q' => 'mobile']))
        ->assertSee('Pemrograman Mobile')
        ->assertDontSee('Basis Data');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index'))
        ->assertSee('Belum ada kategori tutorial')
        ->assertSee('<a href="'.route('admin.kategori-tutorial.create').'" class="btn btn-success tombol-tambah"', false);

    KategoriTutorial::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index', ['q' => 'tidak-ada']))
        ->assertSee('Kategori tutorial tidak ditemukan');
});

it('menambah kategori tutorial', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.create'))
        ->assertOk()
        ->assertSee('name="nama_kategori"', false);

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-tutorial.store'), ['nama_kategori' => 'Desain UI'])
        ->assertRedirect(route('admin.kategori-tutorial.index'))
        ->assertSessionHas('success');

    expect(KategoriTutorial::sole()->nama_kategori)->toBe('Desain UI');
});

it('memperbarui kategori tutorial dengan nama sendiri maupun nama baru', function () {
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.edit', $kategori))
        ->assertOk()
        ->assertSee('value="Pemrograman Web"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-tutorial.update', $kategori), ['nama_kategori' => 'Pemrograman Web'])
        ->assertRedirect(route('admin.kategori-tutorial.index'))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-tutorial.update', $kategori), ['nama_kategori' => 'Pemrograman Web & API'])
        ->assertSessionHasNoErrors();

    expect($kategori->refresh()->nama_kategori)->toBe('Pemrograman Web & API');
});

it('menolak nama yang sudah dipakai kategori lain saat mengubah', function () {
    KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data']);

    $this->actingAs($this->admin)
        ->put(route('admin.kategori-tutorial.update', $kategori), ['nama_kategori' => 'Pemrograman Web'])
        ->assertSessionHasErrors(['nama_kategori' => 'Nama kategori sudah dipakai kategori lain.']);
});

it('boleh memakai ulang nama kategori yang sudah dihapus', function () {
    KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.kategori-tutorial.store'), ['nama_kategori' => 'Basis Data'])
        ->assertSessionHasNoErrors();

    expect(KategoriTutorial::sole()->nama_kategori)->toBe('Basis Data');
});

it('memvalidasi nama kategori', function (mixed $nama, string $pesan) {
    KategoriTutorial::factory()->create(['nama_kategori' => 'Sudah Ada']);

    $this->actingAs($this->admin)
        ->from(route('admin.kategori-tutorial.create'))
        ->post(route('admin.kategori-tutorial.store'), ['nama_kategori' => $nama])
        ->assertRedirect(route('admin.kategori-tutorial.create'))
        ->assertSessionHasErrors(['nama_kategori' => $pesan]);

    expect(KategoriTutorial::count())->toBe(1);
})->with([
    'kosong' => ['', 'Kolom nama kategori wajib diisi.'],
    'terlalu panjang' => [str_repeat('a', 101), 'Kolom nama kategori tidak boleh lebih dari 100 karakter.'],
    'sudah dipakai' => ['Sudah Ada', 'Nama kategori sudah dipakai kategori lain.'],
]);

it('menghapus kategori tutorial secara soft delete', function () {
    $kategori = KategoriTutorial::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-tutorial.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-tutorial.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('menampilkan jumlah tutorial per kategori tanpa menghitung tutorial terhapus', function () {
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);
    Tutorial::factory()->count(2)->for($kategori)->create();
    Tutorial::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.kategori-tutorial.index'))
        ->assertSeeInOrder(['Pemrograman Web', '<td style="text-align:center">2</td>', 'Jumlah Tutorial</th>', '<td>2</td>'], false)
        ->assertSee('masih dipakai 2 tutorial sehingga tidak bisa dihapus.', false);
});

it('menolak menghapus kategori yang masih dipakai tutorial', function () {
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web']);
    Tutorial::factory()->for($kategori)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-tutorial.destroy', $kategori))
        ->assertRedirect(route('admin.kategori-tutorial.index'))
        ->assertSessionHas('error', 'Kategori “Pemrograman Web” masih dipakai tutorial. Pindahkan atau hapus tutorialnya lebih dulu.');

    expect($kategori->refresh()->trashed())->toBeFalse();
});

it('boleh menghapus kategori yang tutorialnya sudah dihapus semua', function () {
    $kategori = KategoriTutorial::factory()->create();
    Tutorial::factory()->for($kategori)->create()->delete();

    $this->actingAs($this->admin)
        ->delete(route('admin.kategori-tutorial.destroy', $kategori))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($kategori);
});

it('membuat tiga kategori awal dari seeder tanpa menimpa isian admin', function () {
    $this->seed(KategoriTutorialSeeder::class);

    expect(KategoriTutorial::orderBy('id_kategori_tutorial')->pluck('nama_kategori')->all())
        ->toBe(['Pemrograman Web', 'Pemrograman Mobile', 'Basis Data']);

    KategoriTutorial::query()->where('nama_kategori', 'Basis Data')->update(['nama_kategori' => 'Basis Data Relasional']);

    $this->seed(KategoriTutorialSeeder::class);

    expect(KategoriTutorial::count())->toBe(3)
        ->and(KategoriTutorial::where('nama_kategori', 'Basis Data Relasional')->exists())->toBeTrue()
        ->and(KategoriTutorial::where('nama_kategori', 'Basis Data')->exists())->toBeFalse();
});
