<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\RingkasanService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->ringkasan = app(RingkasanService::class);
});

it('menghitung angka setiap modul tanpa data terhapus', function () {
    Artikel::factory()->terbit()->create();
    Artikel::factory()->draf()->count(2)->create();
    Artikel::factory()->terbit()->create()->delete();
    Portofolio::factory()->count(2)->create();
    Portofolio::factory()->create()->delete();
    // Dihitung dari status: tutorial terbit tanpa materi tetap terbit walau belum tampil di website.
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

it('melewati konten tanpa waktu simpan, mis. yang diisi di luar Eloquent', function () {
    Faq::factory()->create(['pertanyaan' => 'Tanya?']);
    $hero = Hero::factory()->create(['judul' => 'Tanpa waktu']);
    DB::table('tb_hero')->where('id_hero', $hero->id_hero)->update(['updated_at' => null]);

    expect($this->ringkasan->terakhirDiperbarui()->pluck('judul')->all())->toBe(['Tanya?']);
});
