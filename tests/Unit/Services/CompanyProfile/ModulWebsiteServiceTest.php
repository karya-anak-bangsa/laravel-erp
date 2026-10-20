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

it('menganggap modul yang belum punya baris sebagai aktif', function () {
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Tutorial]);

    expect(layananModulBaru()->statusTampil())->toBe([
        'layanan' => true,
        'portofolio' => true,
        'tutorial' => false,
        'artikel' => true,
        'faq' => true,
        'ajakan' => true,
    ]);
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
    $modul->statusTampil();

    expect(DB::getQueryLog())->toHaveCount(1);
});

it('menganggap semua modul aktif bila database tidak terbaca agar halaman tetap utuh', function () {
    Log::spy();
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Faq]);
    Cache::forget(ModulWebsite::KUNCI_CACHE);
    config(['database.connections.rusak' => [...config('database.connections.mysql'), 'database' => 'laravel_erp_tidak_ada']]);
    $koneksiAsli = DB::getDefaultConnection();
    DB::setDefaultConnection('rusak');

    try {
        $status = layananModulBaru()->statusTampil();
    } finally {
        // Rollback RefreshDatabase saat teardown memakai koneksi bawaan.
        DB::setDefaultConnection($koneksiAsli);
    }

    expect($status)->each->toBeTrue();
    Log::shouldHaveReceived('warning')->withArgs(fn (string $pesan) => str_contains($pesan, 'tidak terbaca'))->once();
});
