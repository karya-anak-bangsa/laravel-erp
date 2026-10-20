<?php

use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\KategoriTutorialService;

beforeEach(function () {
    $this->kategori = app(KategoriTutorialService::class);
});

it('menyimpan kategori baru beserta slug dari namanya', function () {
    $kategori = $this->kategori->simpan(['nama_kategori' => 'Web & Mobile']);

    expect($kategori->exists)->toBeTrue()
        ->and($kategori->slug)->toBe('web-mobile');
});

it('memberi akhiran angka bila slug sudah dipakai kategori lain, termasuk yang terhapus', function () {
    KategoriTutorial::factory()->create(['slug' => 'basis-data']);
    KategoriTutorial::factory()->create(['slug' => 'basis-data-2'])->delete();

    expect($this->kategori->buatSlug('Basis Data'))->toBe('basis-data-3')
        ->and($this->kategori->buatSlug('???'))->toBe('kategori');
});

it('mempertahankan slug bila nama tidak berubah', function () {
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data', 'slug' => 'slug-lama']);

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Basis Data']);

    expect($kategori->refresh()->slug)->toBe('slug-lama');
});

it('memperbarui slug tanpa bentrok selama kategori belum punya tutorial tayang', function () {
    KategoriTutorial::factory()->create(['slug' => 'pemrograman-web']);
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Web', 'slug' => 'web']);
    // Draf, tutorial terbit tanpa materi, dan tutorial terhapus belum tayang, jadi tidak mengunci slug.
    Tutorial::factory()->draf()->for($kategori)->has(MateriTutorial::factory(), 'materiTutorial')->create();
    Tutorial::factory()->terbit()->for($kategori)->create();
    Tutorial::factory()->terbit()->for($kategori)->has(MateriTutorial::factory(), 'materiTutorial')->create()->delete();

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Pemrograman Web']);

    expect($kategori->refresh())
        ->nama_kategori->toBe('Pemrograman Web')
        ->slug->toBe('pemrograman-web-2');
});

it('mengunci slug kategori yang punya tutorial tayang meskipun namanya diubah', function () {
    $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data', 'slug' => 'basis-data']);
    Tutorial::factory()->terbit()->for($kategori)->has(MateriTutorial::factory(), 'materiTutorial')->create();

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Basis Data Relasional']);

    expect($kategori->refresh())
        ->nama_kategori->toBe('Basis Data Relasional')
        ->slug->toBe('basis-data');
});
