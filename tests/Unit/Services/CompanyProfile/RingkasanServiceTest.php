<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Template;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\RingkasanService;

beforeEach(function () {
    $this->ringkasan = app(RingkasanService::class);
});

it('menghitung angka setiap modul tanpa data terhapus', function () {
    Artikel::factory()->terbit()->create();
    Artikel::factory()->draf()->count(2)->create();
    Artikel::factory()->terbit()->create()->delete();
    Portofolio::factory()->count(2)->create();
    Portofolio::factory()->create()->delete();
    // Dihitung dari status: tutorial terbit tanpa materi tetap terbit (belum tampil, lihat kartu Belum tampil).
    Tutorial::factory()->terbit()->has(MateriTutorial::factory()->count(3), 'materiTutorial')->create();
    Tutorial::factory()->terbit()->create();
    Tutorial::factory()->draf()->has(MateriTutorial::factory(), 'materiTutorial')->create();
    Hero::factory()->aktif()->create();
    Hero::factory()->create();
    Hero::factory()->create()->delete();

    expect($this->ringkasan->statistik())
        ->artikel->toMatchArray(['terbit' => 1, 'draf' => 2])
        ->portofolio->toMatchArray(['jumlah' => 2])
        ->tutorial->toBe(['terbit' => 2, 'draf' => 1, 'materi' => 4])
        ->layanan->toBe(0)
        ->faq->toBe(0)
        ->hero->toBe(['jumlah' => 2, 'aktif' => 1]);
});

it('mengurutkan konten terakhir diperbarui lintas modul dan membatasi jumlahnya', function () {
    $hero = Hero::factory()->create(['judul' => 'Hero', 'updated_at' => '2026-10-18 08:00:00']);
    $materi = MateriTutorial::factory()->create(['judul' => 'Materi', 'updated_at' => '2026-10-20 08:00:00']);
    $faq = Faq::factory()->create(['pertanyaan' => 'Tanya?', 'updated_at' => '2026-10-19 08:00:00']);
    // Tutorial pemilik materi dibuat factory dengan waktu sekarang; dimundurkan agar tidak ikut di urutan atas.
    Tutorial::query()->whereKey($materi->id_tutorial)->update(['updated_at' => '2026-10-01 08:00:00']);

    $hasil = $this->ringkasan->terakhirDiperbarui(3);

    expect($hasil->pluck('judul')->all())->toBe(['Materi', 'Tanya?', 'Hero'])
        ->and($hasil->pluck('modul')->all())->toBe(['Materi Tutorial', 'FAQ', 'Hero'])
        ->and($hasil->pluck('url')->all())->toBe([
            route('admin.materi-tutorial.edit', $materi),
            route('admin.faq.edit', $faq),
            route('admin.hero.edit', $hero),
        ]);
});

it('mengambil artikel & tutorial yang belum tampil, terakhir disimpan lebih dulu, dan membatasi jumlahnya', function () {
    Artikel::factory()->draf()->create(['judul' => 'Draf lama', 'updated_at' => '2026-10-01 08:00:00']);
    $artikel = Artikel::factory()->draf()->create(['judul' => 'Draf baru', 'updated_at' => '2026-10-20 08:00:00']);
    $tutorial = Tutorial::factory()->terbit()->create(['judul' => 'Tanpa materi', 'updated_at' => '2026-10-19 08:00:00']);
    Tutorial::factory()->draf()->create(['judul' => 'Tutorial draf', 'updated_at' => '2026-10-10 08:00:00']);
    // Sudah tampil atau terhapus: tidak ikut walau lebih baru.
    Artikel::factory()->terbit()->create(['updated_at' => '2026-10-20 09:00:00']);
    Tutorial::factory()->terbit()->has(MateriTutorial::factory(), 'materiTutorial')->create(['updated_at' => '2026-10-20 09:00:00']);
    Artikel::factory()->draf()->create(['updated_at' => '2026-10-20 10:00:00'])->delete();

    $hasil = $this->ringkasan->belumTampil(3);

    expect($hasil->pluck('judul')->all())->toBe(['Draf baru', 'Tanpa materi', 'Tutorial draf'])
        ->and($hasil->pluck('modul')->all())->toBe(['Artikel', 'Tutorial', 'Tutorial'])
        ->and($hasil->pluck('status')->all())->toBe(['Draf', 'Belum ada materi', 'Draf'])
        ->and($hasil->pluck('url')->take(2)->all())->toBe([
            route('admin.artikel.edit', $artikel),
            route('admin.tutorial.edit', $tutorial),
        ]);
});

it('membaca hero aktif, template aktif per jenis, dan izin mesin pencari', function () {
    Hero::factory()->aktif()->create(['judul' => 'Hero Aktif']);
    Template::factory()->monochrome()->aktif()->create(['nama' => 'Mono Aktif']);
    Template::factory()->create(['nama' => 'Warna Nonaktif']);
    config(['seo.indeks' => false]);

    $status = $this->ringkasan->statusSitus();

    expect($status['hero']?->judul)->toBe('Hero Aktif')
        ->and($status['template']->map->nama->all())->toBe(['monochrome' => 'Mono Aktif'])
        ->and($status['indeks'])->toBeFalse();
});

it('menghitung modul website yang nonaktif', function () {
    expect($this->ringkasan->statusSitus()['modulNonaktif'])->toBe(0);

    ModulWebsite::factory()->create(['jenis' => JenisModul::Artikel]);
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Tutorial]);
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Ajakan]);

    expect($this->ringkasan->statusSitus()['modulNonaktif'])->toBe(2);
});
