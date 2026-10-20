<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\Pengguna;
use Database\Seeders\ModulWebsiteSeeder;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

it('mengarahkan tamu ke halaman login', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Portofolio]);

    $this->get(route('admin.modul-website.index'))->assertRedirect(route('login'));
    $this->get(route('admin.modul-website.edit', $modul))->assertRedirect(route('login'));
    $this->put(route('admin.modul-website.update', $modul), ['urutan_ke' => 1, 'status_aktif' => '0'])->assertRedirect(route('login'));

    expect($modul->fresh()->status_aktif)->toBeTrue();
});

it('menampilkan semua modul menurut urutannya beserta tempat tampil dan statusnya', function () {
    ModulWebsite::factory()->create(['jenis' => JenisModul::Layanan, 'urutan_ke' => 3]);
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Tutorial, 'urutan_ke' => 2]);
    ModulWebsite::factory()->create(['jenis' => JenisModul::Faq, 'urutan_ke' => 1]);

    $this->actingAs($this->admin)
        ->get(route('admin.modul-website.index'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSee('<div class="card-title">Manajemen Modul</div>', false)
        ->assertSee('class="nav-link active" href="'.route('admin.modul-website.index').'"', false)
        ->assertSeeInOrder([
            '<td class="kolom-pas cell-strong">1</td>', '>FAQ</div>', 'Menu navbar &amp; Tautan Cepat footer · Seksi FAQ di beranda',
            '<td class="kolom-pas cell-strong">2</td>', '>Tutorial</div>', 'Menu navbar &amp; Tautan Cepat footer</div>', '<span class="chip chip-red">Nonaktif</span>',
            '<td class="kolom-pas cell-strong">3</td>', '>Layanan</div>', '<span class="chip chip-green">Aktif</span>',
        ], false)
        // Baris tetap per modul: tanpa kartu filter, tombol tambah, dan hapus.
        ->assertDontSee('kartu-filter', false)
        ->assertDontSee('tombol-tambah', false)
        ->assertDontSee('data-confirm-label="Ya, hapus"', false);
});

it('tidak menyediakan tambah dan hapus modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);

    expect(Route::has('admin.modul-website.create'))->toBeFalse()
        ->and(Route::has('admin.modul-website.store'))->toBeFalse()
        ->and(Route::has('admin.modul-website.destroy'))->toBeFalse();

    $this->actingAs($this->admin)->post(route('admin.modul-website.index'), ['jenis' => 'faq'])->assertMethodNotAllowed();
    $this->actingAs($this->admin)->delete(route('admin.modul-website.update', $modul))->assertMethodNotAllowed();

    expect(ModulWebsite::query()->count())->toBe(1);
});

it('menampilkan form ubah berisi tempat tampil, status, dan urutan', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Artikel, 'urutan_ke' => 5]);

    $this->actingAs($this->admin)
        ->get(route('admin.modul-website.edit', $modul))
        ->assertOk()
        ->assertSee('data-confirm="Status dan urutan modul “Artikel” akan disimpan dan langsung berlaku di website."', false)
        ->assertSeeInOrder([
            'Modul Artikel', '<li>Menu navbar &amp; Tautan Cepat footer</li>', '<li>Seksi Artikel di beranda</li>',
            'name="status_aktif" value="1" checked', 'Tampilkan di website',
            'Halaman artikel tetap bisa dibuka lewat alamatnya. Isinya tetap bisa dikelola di menu Artikel.',
            '<div class="card-title">Urutan</div>', 'name="urutan_ke"', 'value="5"',
        ], false);
});

it('menyimpan status dan urutan modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Portofolio, 'urutan_ke' => 2]);

    // Switch dimatikan: hanya hidden input bernilai 0 yang terkirim.
    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['urutan_ke' => '7', 'status_aktif' => '0'])
        ->assertRedirect(route('admin.modul-website.index'))
        ->assertSessionHas('success', 'Modul Portofolio berhasil diperbarui dan disembunyikan dari website.');

    expect($modul->fresh())->status_aktif->toBeFalse()->urutan_ke->toBe(7);

    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['urutan_ke' => '2', 'status_aktif' => '1'])
        ->assertSessionHas('success', 'Modul Portofolio berhasil diperbarui dan tampil di website.');

    expect($modul->fresh())->status_aktif->toBeTrue()->urutan_ke->toBe(2);
});

it('memvalidasi urutan', function (mixed $urutan, string $pesan) {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq, 'urutan_ke' => 6]);

    $this->actingAs($this->admin)
        ->from(route('admin.modul-website.edit', $modul))
        ->put(route('admin.modul-website.update', $modul), ['urutan_ke' => $urutan, 'status_aktif' => '1'])
        ->assertRedirect(route('admin.modul-website.edit', $modul))
        ->assertSessionHasErrors(['urutan_ke' => $pesan]);

    expect($modul->fresh()->urutan_ke)->toBe(6);
})->with([
    'kosong' => ['', 'Kolom urutan wajib diisi.'],
    'bukan angka' => ['dua', 'Kolom urutan harus berupa bilangan bulat.'],
    'negatif' => [-1, 'Kolom urutan harus bernilai minimal 0.'],
    'terlalu besar' => [ModulWebsite::URUTAN_MAKS + 1, 'Kolom urutan tidak boleh lebih besar dari '.ModulWebsite::URUTAN_MAKS.'.'],
]);

it('hanya mengubah status dan urutan, bukan jenis modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);

    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['urutan_ke' => '1', 'status_aktif' => '0', 'jenis' => 'artikel']);

    expect($modul->fresh()->jenis)->toBe(JenisModul::Faq);
});

it('mengisi satu baris aktif per modul dengan urutan bawaan dan tidak menimpa isian admin saat dijalankan ulang', function () {
    $this->seed(ModulWebsiteSeeder::class);

    expect(ModulWebsite::query()->berurutan()->get()->map(fn (ModulWebsite $modul) => [$modul->jenis, $modul->urutan_ke, $modul->status_aktif])->all())
        ->toBe([
            [JenisModul::Layanan, 1, true],
            [JenisModul::Portofolio, 2, true],
            [JenisModul::Tutorial, 3, true],
            [JenisModul::Ajakan, 4, true],
            [JenisModul::Artikel, 5, true],
            [JenisModul::Faq, 6, true],
        ]);

    ModulWebsite::query()->where('jenis', JenisModul::Tutorial)->update(['status_aktif' => false, 'urutan_ke' => 9]);
    ModulWebsite::query()->where('jenis', JenisModul::Faq)->delete();
    $this->seed(ModulWebsiteSeeder::class);

    $tutorial = ModulWebsite::query()->where('jenis', JenisModul::Tutorial)->first();

    expect(ModulWebsite::withTrashed()->count())->toBe(count(JenisModul::cases()))
        ->and($tutorial->status_aktif)->toBeFalse()
        ->and($tutorial->urutan_ke)->toBe(9);
});
