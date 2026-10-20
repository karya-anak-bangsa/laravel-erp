<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\KontakKami;
use App\Models\CompanyProfile\MateriTutorial;
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
    // Tayang = terbit dan bermateri; terbit tanpa materi dan draf termasuk belum tayang.
    Tutorial::factory()->terbit()->has(MateriTutorial::factory()->count(3), 'materiTutorial')->create();
    Tutorial::factory()->terbit()->create();
    Tutorial::factory()->draf()->has(MateriTutorial::factory(), 'materiTutorial')->create();
    KontakKami::factory()->belumDibaca()->count(2)->create();
    KontakKami::factory()->dibaca()->create();

    expect($this->ringkasan->statistik())
        ->artikel->toMatchArray(['terbit' => 1, 'draf' => 2])
        ->portofolio->toMatchArray(['jumlah' => 2])
        ->tutorial->toBe(['tayang' => 1, 'belumTayang' => 2, 'materi' => 4])
        ->layanan->toBe(0)
        ->faq->toBe(0)
        ->pesan->toBe(['belumDibaca' => 2, 'jumlah' => 3]);
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

it('mengambil pesan terbaru menurut tanggal masuk', function () {
    KontakKami::factory()->create(['subjek' => 'Lama', 'tanggal' => '2026-10-01 08:00:00']);
    KontakKami::factory()->create(['subjek' => 'Baru', 'tanggal' => '2026-10-20 08:00:00']);
    KontakKami::factory()->create(['subjek' => 'Tengah', 'tanggal' => '2026-10-10 08:00:00']);

    expect($this->ringkasan->pesanTerbaru(2)->pluck('subjek')->all())->toBe(['Baru', 'Tengah']);
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
