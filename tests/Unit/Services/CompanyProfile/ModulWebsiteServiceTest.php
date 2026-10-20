<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use App\Services\CompanyProfile\ModulWebsiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Status diingat per request, jadi setiap "halaman" memakai request baru.
function layananModulBaru(): ModulWebsiteService
{
    app()->instance('request', new Request);

    return new ModulWebsiteService;
}

it('menampilkan semua modul dengan urutan bawaan bila belum ada baris', function () {
    expect(layananModulBaru()->urutanTampil())->toBe(JenisModul::cases());
});

it('membuang modul nonaktif dan mengurutkan menurut urutan_ke, angka sama mengikuti urutan bawaan', function () {
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Tutorial]);
    ModulWebsite::factory()->create(['jenis' => JenisModul::Faq, 'urutan_ke' => 1]);
    ModulWebsite::factory()->create(['jenis' => JenisModul::Artikel, 'urutan_ke' => 1]);
    // Layanan, Portofolio, dan Ajakan belum punya baris: aktif dengan urutan bawaan 1, 2, dan 3.

    $modul = layananModulBaru();

    expect($modul->urutanTampil())->toBe([
        JenisModul::Layanan, JenisModul::Artikel, JenisModul::Faq, JenisModul::Portofolio, JenisModul::Ajakan,
    ])
        ->and($modul->tampil(JenisModul::Tutorial))->toBeFalse()
        ->and($modul->tampil(JenisModul::Ajakan))->toBeTrue();
});

it('memakai status terbaru setelah admin mengubahnya', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Artikel]);

    expect(layananModulBaru()->tampil(JenisModul::Artikel))->toBeTrue();

    // Cache dibuang lewat event model, jadi halaman berikutnya langsung membaca status baru.
    $modul->update(['status_aktif' => false]);

    expect(layananModulBaru()->tampil(JenisModul::Artikel))->toBeFalse();
});

it('membaca database sekali per request meskipun dipanggil berkali-kali', function () {
    ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);
    $modul = layananModulBaru();

    DB::enableQueryLog();
    $modul->tampil(JenisModul::Faq);
    $modul->tampil(JenisModul::Layanan);
    $modul->urutanTampil();

    expect(DB::getQueryLog())->toHaveCount(1);
});

it('menampilkan semua modul dengan urutan bawaan bila database tidak terbaca agar halaman tetap utuh', function () {
    Log::spy();
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Faq, 'urutan_ke' => 9]);
    Cache::forget(ModulWebsite::KUNCI_CACHE);
    config(['database.connections.rusak' => [...config('database.connections.mysql'), 'database' => 'laravel_erp_tidak_ada']]);
    $koneksiAsli = DB::getDefaultConnection();
    DB::setDefaultConnection('rusak');

    try {
        $urutan = layananModulBaru()->urutanTampil();
    } finally {
        // Rollback RefreshDatabase saat teardown memakai koneksi bawaan.
        DB::setDefaultConnection($koneksiAsli);
    }

    expect($urutan)->toBe(JenisModul::cases());
    Log::shouldHaveReceived('warning')->withArgs(fn (string $pesan) => str_contains($pesan, 'tidak terbaca'))->once();
});
