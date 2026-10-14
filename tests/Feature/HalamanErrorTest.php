<?php

use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\IdentitasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Detail error disembunyikan seperti di produksi agar halaman kustom yang tampil.
    config()->set('app.debug', false);
    Storage::fake('public');
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Galat']);
});

describe('halaman error publik', function () {
    it('menampilkan 404 bergaya situs lengkap dengan navbar, footer, dan tombol ke beranda', function () {
        Layanan::factory()->create(['judul' => 'Layanan Uji Footer']);

        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('<html lang="id" data-tema="full_color">', false)
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('<title>Halaman tidak ditemukan — PT Uji Galat</title>', false)
            ->assertSee('aria-label="Menu utama"', false)
            ->assertSee('Layanan Uji Footer')
            ->assertSee('id="tombol-wa"', false)
            ->assertSee('Ke beranda')
            ->assertSee('data-kembali', false)
            ->assertDontSee('Ke dashboard')
            ->assertDontSee('/admin')
            ->assertDontSee('/login');
    });

    it('mengikuti tema pilihan pengunjung dari cookie walau alamatnya tidak dikenal', function () {
        $this->withUnencryptedCookie('tema', 'monochrome')
            ->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('<html lang="id" data-tema="monochrome">', false)
            ->assertSee('data-pilih-tema="monochrome" aria-pressed="true"', false);
    });

    it('menampilkan kode dan judul sesuai status dengan tombol ke beranda', function (int $kode, string $judul) {
        Route::middleware('web')->get('/uji-error', fn () => abort($kode));

        $this->get('/uji-error')
            ->assertStatus($kode)
            ->assertSee('</span>'.$kode.'</p>', false)
            ->assertSee($judul)
            ->assertSee('Ke beranda')
            ->assertDontSee('Masuk kembali')
            ->assertDontSee('Ke dashboard');
    })->with([
        [403, 'Akses ditolak'],
        [413, 'Permintaan tidak dapat diproses'],
        [419, 'Sesi telah berakhir'],
        [429, 'Terlalu banyak permintaan'],
        [502, 'Layanan sedang terganggu'],
    ]);

    it('menampilkan halaman publik untuk metode yang tidak didukung', function () {
        $this->put('/')
            ->assertStatus(405)
            ->assertSee('Permintaan tidak dapat diproses')
            ->assertSee('Ke beranda');
    });

    it('hanya menawarkan muat ulang saat pemeliharaan', function () {
        Route::middleware('web')->get('/uji-error', fn () => abort(503));

        $this->get('/uji-error')
            ->assertStatus(503)
            ->assertSee('Sedang dalam pemeliharaan')
            ->assertSee('<a href="'.url('/uji-error').'" class="btn btn-besar">Muat ulang</a>', false)
            ->assertDontSee('Ke beranda')
            ->assertDontSee('data-kembali', false);
    });

    it('menampilkan 500 tanpa detail error', function () {
        Route::middleware('web')->get('/uji-error', fn () => throw new RuntimeException('detail-rahasia'));

        $this->get('/uji-error')
            ->assertStatus(500)
            ->assertSee('Terjadi kesalahan server')
            ->assertSee('Ke beranda')
            ->assertDontSee('detail-rahasia');
    });

    it('tetap tampil ringkas tanpa navbar dan footer bila data situs tidak terbaca', function () {
        Log::spy();
        $this->mock(IdentitasService::class)->shouldReceive('untukSitus')->andThrow(new RuntimeException('database-mati'));
        Route::middleware('web')->get('/uji-error', fn () => throw new RuntimeException('detail-rahasia'));

        $this->get('/uji-error')
            ->assertStatus(500)
            ->assertSee('<title>Terjadi kesalahan server — '.config('perusahaan.nama').'</title>', false)
            ->assertSee('class="logo-situs merek-ringkas"', false)
            ->assertSee('Ke beranda')
            ->assertDontSee('aria-label="Menu utama"', false)
            ->assertDontSee('id="tombol-wa"', false)
            ->assertDontSee('detail-rahasia')
            ->assertDontSee('database-mati');

        Log::shouldHaveReceived('warning')->once();
    });
});

describe('halaman error panel admin', function () {
    it('tetap memakai layout admin untuk alamat panel admin', function () {
        $this->get('/admin/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('Ke dashboard')
            ->assertDontSee('data-tema', false)
            ->assertDontSee('Ke beranda');
    });

    it('menampilkan tombol sesuai kode status', function (int $kode, string $judul, string $aksi) {
        Route::middleware('web')->get('/admin/uji-error', fn () => abort($kode));

        $this->get('/admin/uji-error')
            ->assertStatus($kode)
            ->assertSee($judul)
            ->assertSee($aksi)
            ->assertDontSee('data-tema', false);
    })->with([
        [403, 'Akses ditolak', 'Ke dashboard'],
        [419, 'Sesi telah berakhir', 'Masuk kembali'],
        [429, 'Terlalu banyak permintaan', 'Ke dashboard'],
        [500, 'Terjadi kesalahan server', 'Ke dashboard'],
        [502, 'Layanan sedang terganggu', 'Ke dashboard'],
        [503, 'Sedang dalam pemeliharaan', 'Muat ulang'],
    ]);

    it('memakai layout admin untuk logout yang dibuka tanpa POST', function () {
        $this->get('/logout')
            ->assertStatus(405)
            ->assertSee('Permintaan tidak dapat diproses')
            ->assertSee('Ke dashboard')
            ->assertDontSee('data-tema', false);
    });
});
