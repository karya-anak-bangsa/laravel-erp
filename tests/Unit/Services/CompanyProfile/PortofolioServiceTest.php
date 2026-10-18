<?php

use App\Models\CompanyProfile\GambarPortofolio;
use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\PortofolioService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->portofolio = app(PortofolioService::class);
});

function dataPortofolioUji(array $timpa = []): array
{
    return [
        'judul' => 'Portofolio Uji',
        'kategori' => 'Website',
        'deskripsi' => 'Deskripsi portofolio uji.',
        'gambar' => UploadedFile::fake()->image('portofolio.webp'),
        ...$timpa,
    ];
}

it('membuat slug dari judul', function () {
    expect($this->portofolio->buatSlug('Website Profil: Sekolah & Kampus!'))->toBe('website-profil-sekolah-kampus');
});

it('memberi akhiran angka berurutan bila slug sudah dipakai', function () {
    Portofolio::factory()->create(['slug' => 'aplikasi-klinik']);
    Portofolio::factory()->create(['slug' => 'aplikasi-klinik-2']);

    expect($this->portofolio->buatSlug('Aplikasi Klinik'))->toBe('aplikasi-klinik-3');
});

it('menghitung slug portofolio yang sudah dihapus', function () {
    Portofolio::factory()->create(['slug' => 'aplikasi-klinik'])->delete();

    expect($this->portofolio->buatSlug('Aplikasi Klinik'))->toBe('aplikasi-klinik-2');
});

it('memakai slug cadangan bila judul tidak menghasilkan slug', function () {
    expect($this->portofolio->buatSlug('???'))->toBe('portofolio');
});

it('membatasi panjang slug agar akhiran angka tetap muat', function () {
    $slug = $this->portofolio->buatSlug(str_repeat('kata ', 60));

    expect(strlen($slug))->toBeLessThanOrEqual(200)
        ->and($slug)->not->toEndWith('-');
});

it('menyimpan portofolio baru beserta slug dan gambarnya', function () {
    $portofolio = $this->portofolio->simpan(dataPortofolioUji());

    expect($portofolio->exists)->toBeTrue()
        ->and($portofolio->slug)->toBe('portofolio-uji')
        ->and($portofolio->gambar)->toStartWith('company-profile/portofolio/');
    Storage::disk('public')->assertExists($portofolio->gambar);
});

it('mempertahankan slug walau judul diubah', function () {
    $portofolio = Portofolio::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['judul' => 'Judul Baru', 'gambar' => null, 'kategori' => 'Mobile Apps']));

    // Portofolio tanpa draf langsung tayang, jadi tautannya mungkin sudah tersebar.
    expect($portofolio->refresh()->slug)->toBe('judul-lama')
        ->and($portofolio->judul)->toBe('Judul Baru')
        ->and($portofolio->kategori)->toBe('Mobile Apps');
});

it('mengganti gambar dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/portofolio/lama.webp', 'isi');
    $portofolio = Portofolio::factory()->create(['gambar' => 'company-profile/portofolio/lama.webp']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($portofolio->refresh()->gambar)->toEndWith('.jpg');
    Storage::disk('public')->assertMissing('company-profile/portofolio/lama.webp');
});

it('menyimpan gambar tambahan urut unggah bersama portofolio baru', function () {
    $portofolio = $this->portofolio->simpan(dataPortofolioUji([
        'galeri' => [UploadedFile::fake()->image('kedua.png'), UploadedFile::fake()->image('ketiga.jpg')],
    ]));

    $gambar = $portofolio->gambarPortofolio()->get();
    expect($gambar)->toHaveCount(2)
        ->and($gambar[0]->gambar)->toStartWith(Portofolio::FOLDER.'/')->toEndWith('.png')
        ->and($gambar[1]->gambar)->toEndWith('.jpg');
    $gambar->each(fn (GambarPortofolio $item) => Storage::disk('public')->assertExists($item->gambar));
});

it('membuang gambar tambahan yang tidak dipertahankan beserta berkasnya dan menambah gambar baru', function () {
    $portofolio = Portofolio::factory()->create();
    Storage::disk('public')->put('company-profile/portofolio/buang.webp', 'isi');
    Storage::disk('public')->put('company-profile/portofolio/tetap.webp', 'isi');
    GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/buang.webp']);
    $tetap = GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/tetap.webp']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji([
        'gambar' => null,
        'galeri_lama' => [$tetap->id_gambar_portofolio],
        // Baris repeater tanpa berkas terkirim null.
        'galeri' => [null, UploadedFile::fake()->image('baru.webp')],
    ]));

    expect($portofolio->gambarPortofolio()->pluck('gambar')->all())
        ->toHaveCount(2)
        ->sequence(
            fn ($gambar) => $gambar->toBe('company-profile/portofolio/tetap.webp'),
            fn ($gambar) => $gambar->toEndWith('.webp')->not->toBe('company-profile/portofolio/buang.webp'),
        );
    Storage::disk('public')->assertMissing('company-profile/portofolio/buang.webp');
    Storage::disk('public')->assertExists('company-profile/portofolio/tetap.webp');
});

it('membiarkan gambar tambahan lama bila daftar yang dipertahankan tidak diberikan', function () {
    $portofolio = Portofolio::factory()->create();
    GambarPortofolio::factory()->count(2)->for($portofolio)->create();

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['gambar' => null]));

    expect($portofolio->gambarPortofolio()->count())->toBe(2);
});

it('membersihkan gambar baru bila penyimpanan gagal', function () {
    // Kategori melebihi VARCHAR(50) sehingga INSERT gagal di MySQL.
    expect(fn () => $this->portofolio->simpan(dataPortofolioUji([
        'kategori' => str_repeat('a', 100),
        'galeri' => [UploadedFile::fake()->image('kedua.png')],
    ])))->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles(Portofolio::FOLDER))->toBe([])
        ->and(GambarPortofolio::count())->toBe(0);
});

it('mempertahankan gambar tambahan yang akan dibuang bila penyimpanan perubahan gagal', function () {
    $portofolio = Portofolio::factory()->create();
    Storage::disk('public')->put('company-profile/portofolio/buang.webp', 'isi');
    GambarPortofolio::factory()->for($portofolio)->create(['gambar' => 'company-profile/portofolio/buang.webp']);

    expect(fn () => $this->portofolio->perbarui($portofolio, dataPortofolioUji([
        'kategori' => str_repeat('a', 100),
        'gambar' => null,
        // Tidak ada gambar lama yang dipertahankan.
        'galeri_lama' => [],
        'galeri' => [UploadedFile::fake()->image('baru.png')],
    ])))->toThrow(QueryException::class);

    // Transaksi dibatalkan: baris & berkas lama tetap, unggahan baru dibersihkan.
    expect($portofolio->gambarPortofolio()->pluck('gambar')->all())->toBe(['company-profile/portofolio/buang.webp'])
        ->and(Storage::disk('public')->allFiles(Portofolio::FOLDER))->toBe(['company-profile/portofolio/buang.webp']);
});
