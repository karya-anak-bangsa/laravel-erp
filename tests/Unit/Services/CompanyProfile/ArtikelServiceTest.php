<?php

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\ArtikelService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->artikel = app(ArtikelService::class);
});

function dataArtikelUji(array $timpa = []): array
{
    return [
        'id_kategori_artikel' => KategoriArtikel::factory()->create()->id_kategori_artikel,
        'judul' => 'Artikel Uji',
        'deskripsi' => '<h2>Bagian</h2><p>Isi artikel uji.</p>',
        'gambar' => UploadedFile::fake()->image('artikel.webp'),
        'tanggal' => '2026-10-09',
        'status_publikasi' => StatusPublikasi::Terbit->value,
        ...$timpa,
    ];
}

it('menyimpan artikel baru beserta slug dan gambarnya', function () {
    $artikel = $this->artikel->simpan(dataArtikelUji());

    expect($artikel->exists)->toBeTrue()
        ->and($artikel->slug)->toBe('artikel-uji')
        ->and($artikel->status_publikasi)->toBe(StatusPublikasi::Terbit)
        ->and($artikel->gambar)->toStartWith('company-profile/artikel/');
    Storage::disk('public')->assertExists($artikel->gambar);
});

it('memberi akhiran angka bila slug sudah dipakai artikel lain, termasuk yang terhapus', function () {
    Artikel::factory()->create(['slug' => 'artikel-uji']);
    Artikel::factory()->create(['slug' => 'artikel-uji-2'])->delete();

    expect($this->artikel->buatSlug('Artikel Uji'))->toBe('artikel-uji-3')
        ->and($this->artikel->buatSlug('???'))->toBe('artikel');
});

it('tidak menghitung slug portofolio sebagai bentrok', function () {
    Portofolio::factory()->create(['slug' => 'artikel-uji']);

    expect($this->artikel->buatSlug('Artikel Uji'))->toBe('artikel-uji');
});

it('mempertahankan slug bila judul tidak berubah', function () {
    $artikel = Artikel::factory()->create(['judul' => 'Artikel Uji', 'slug' => 'slug-lama']);

    $this->artikel->perbarui($artikel, dataArtikelUji(['gambar' => null, 'status_publikasi' => 'draf']));

    expect($artikel->refresh()->slug)->toBe('slug-lama')
        ->and($artikel->status_publikasi)->toBe(StatusPublikasi::Draf);
});

it('memperbarui slug tanpa bentrok dengan artikel lain saat judul berubah', function () {
    Artikel::factory()->create(['slug' => 'judul-baru']);
    $artikel = Artikel::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->artikel->perbarui($artikel, dataArtikelUji(['judul' => 'Judul Baru', 'gambar' => null]));

    expect($artikel->refresh()->slug)->toBe('judul-baru-2');
});

it('mengganti gambar dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/artikel/lama.webp', 'isi');
    $artikel = Artikel::factory()->create(['gambar' => 'company-profile/artikel/lama.webp']);

    $this->artikel->perbarui($artikel, dataArtikelUji(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($artikel->refresh()->gambar)->toEndWith('.jpg');
    Storage::disk('public')->assertMissing('company-profile/artikel/lama.webp');
});

it('membersihkan gambar baru bila penyimpanan gagal', function () {
    // Kategori tidak ada sehingga FK menolak INSERT.
    expect(fn () => $this->artikel->simpan(dataArtikelUji(['id_kategori_artikel' => 999999])))
        ->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles(Artikel::FOLDER))->toBe([]);
});
