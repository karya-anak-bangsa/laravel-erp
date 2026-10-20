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
    $this->put(route('admin.modul-website.update', $modul), ['status_aktif' => '0'])->assertRedirect(route('login'));

    expect($modul->fresh()->status_aktif)->toBeTrue();
});

it('menampilkan semua modul seurut menu navbar beserta tempat tampil dan statusnya', function () {
    // Dibuat terbalik: urutan daftar mengikuti JenisModul, bukan urutan baris.
    foreach (array_reverse(JenisModul::cases()) as $jenis) {
        ModulWebsite::factory()->create(['jenis' => $jenis, 'status_aktif' => $jenis !== JenisModul::Tutorial]);
    }

    $this->actingAs($this->admin)
        ->get(route('admin.modul-website.index'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSee('<div class="card-title">Manajemen Modul</div>', false)
        ->assertSee('class="nav-link active" href="'.route('admin.modul-website.index').'"', false)
        ->assertSeeInOrder(['>Layanan</div>', '>Portofolio</div>', '>Tutorial</div>', '>Artikel</div>', '>FAQ</div>', '>Ajakan</div>'], false)
        ->assertSeeInOrder([
            '>Tutorial</div>', 'Menu navbar &amp; Tautan Cepat footer · Halaman daftar, kategori, tutorial, &amp; materi',
            '<span class="chip chip-red">Nonaktif</span>', '>Artikel</div>', '<span class="chip chip-green">Aktif</span>',
        ], false)
        // Baris tetap per modul: tanpa kartu filter, tombol tambah, dan hapus.
        ->assertDontSee('kartu-filter', false)
        ->assertDontSee('tombol-tambah', false)
        ->assertDontSee('data-confirm-label="Ya, hapus"', false);
});

it('tidak menyediakan tambah dan hapus modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);

    expect(Route::has(['admin.modul-website.create']))->toBeFalse()
        ->and(Route::has(['admin.modul-website.store']))->toBeFalse()
        ->and(Route::has(['admin.modul-website.destroy']))->toBeFalse();

    $this->actingAs($this->admin)->post(route('admin.modul-website.index'), ['jenis' => 'faq'])->assertMethodNotAllowed();
    $this->actingAs($this->admin)->delete(route('admin.modul-website.update', $modul))->assertMethodNotAllowed();

    expect(ModulWebsite::query()->count())->toBe(1);
});

it('menampilkan form ubah berisi tempat tampil dan akibat bila nonaktif', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Artikel]);

    $this->actingAs($this->admin)
        ->get(route('admin.modul-website.edit', $modul))
        ->assertOk()
        ->assertSee('data-confirm="Status modul “Artikel” akan disimpan dan langsung berlaku di website."', false)
        ->assertSeeInOrder([
            'Modul Artikel', '<li>Menu navbar &amp; Tautan Cepat footer</li>', '<li>Seksi Artikel di beranda</li>',
            'name="status_aktif" value="1" checked', 'Tampilkan di website',
            'Halamannya menjadi 404 dan keluar dari sitemap. Isinya tetap bisa dikelola di menu Artikel.',
        ], false);
});

it('menonaktifkan dan mengaktifkan kembali modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Portofolio]);

    // Switch dimatikan: hanya hidden input bernilai 0 yang terkirim.
    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['status_aktif' => '0'])
        ->assertRedirect(route('admin.modul-website.index'))
        ->assertSessionHas('success', 'Modul Portofolio kini disembunyikan dari website.');

    expect($modul->fresh()->status_aktif)->toBeFalse();

    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['status_aktif' => '1'])
        ->assertSessionHas('success', 'Modul Portofolio kini tampil di website.');

    expect($modul->fresh()->status_aktif)->toBeTrue();
});

it('hanya mengubah status, bukan jenis modul', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);

    $this->actingAs($this->admin)
        ->put(route('admin.modul-website.update', $modul), ['status_aktif' => '0', 'jenis' => 'artikel']);

    expect($modul->fresh()->jenis)->toBe(JenisModul::Faq);
});

it('mengisi satu baris aktif per modul dan tidak menimpa status yang diatur admin saat dijalankan ulang', function () {
    $this->seed(ModulWebsiteSeeder::class);

    expect(ModulWebsite::query()->pluck('status_aktif', 'jenis')->all())
        ->toBe(array_fill_keys(array_column(JenisModul::cases(), 'value'), true));

    ModulWebsite::query()->where('jenis', JenisModul::Tutorial)->update(['status_aktif' => false]);
    ModulWebsite::query()->where('jenis', JenisModul::Faq)->delete();
    $this->seed(ModulWebsiteSeeder::class);

    expect(ModulWebsite::withTrashed()->count())->toBe(count(JenisModul::cases()))
        ->and(ModulWebsite::query()->where('jenis', JenisModul::Tutorial)->first()->status_aktif)->toBeFalse();
});
