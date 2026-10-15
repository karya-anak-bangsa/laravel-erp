<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\CompanyProfile\KategoriArtikelService;

beforeEach(function () {
    $this->kategori = app(KategoriArtikelService::class);
});

it('menyimpan kategori baru beserta slug dari namanya', function () {
    $kategori = $this->kategori->simpan(['nama_kategori' => 'Pelatihan & Sertifikasi']);

    expect($kategori->exists)->toBeTrue()
        ->and($kategori->slug)->toBe('pelatihan-sertifikasi');
});

it('memberi akhiran angka bila slug sudah dipakai kategori lain, termasuk yang terhapus', function () {
    KategoriArtikel::factory()->create(['slug' => 'teknologi']);
    KategoriArtikel::factory()->create(['slug' => 'teknologi-2'])->delete();

    expect($this->kategori->buatSlug('Teknologi'))->toBe('teknologi-3')
        ->and($this->kategori->buatSlug('???'))->toBe('kategori');
});

it('tidak menghitung slug artikel sebagai bentrok', function () {
    Artikel::factory()->create(['slug' => 'teknologi']);

    expect($this->kategori->buatSlug('Teknologi'))->toBe('teknologi');
});

it('mempertahankan slug bila nama tidak berubah', function () {
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'slug-lama']);

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Teknologi']);

    expect($kategori->refresh()->slug)->toBe('slug-lama');
});

it('memperbarui slug tanpa bentrok selama kategori belum punya artikel terbit', function () {
    KategoriArtikel::factory()->create(['slug' => 'berita']);
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Kabar', 'slug' => 'kabar']);
    Artikel::factory()->draf()->for($kategori)->create();
    // Artikel terbit yang sudah dihapus tidak lagi tayang, jadi tidak mengunci slug.
    Artikel::factory()->terbit()->for($kategori)->create()->delete();

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Berita']);

    expect($kategori->refresh())
        ->nama_kategori->toBe('Berita')
        ->slug->toBe('berita-2');
});

it('mengunci slug kategori yang punya artikel terbit meskipun namanya diubah', function () {
    $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'teknologi']);
    Artikel::factory()->terbit()->for($kategori)->create();

    $this->kategori->perbarui($kategori, ['nama_kategori' => 'Teknologi Informasi']);

    expect($kategori->refresh())
        ->nama_kategori->toBe('Teknologi Informasi')
        ->slug->toBe('teknologi');
});
