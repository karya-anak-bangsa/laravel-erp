<?php

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

it('mengabaikan slug milik portofolio itu sendiri', function () {
    $portofolio = Portofolio::factory()->create(['slug' => 'aplikasi-klinik']);

    expect($this->portofolio->buatSlug('Aplikasi Klinik', $portofolio))->toBe('aplikasi-klinik');
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

it('mempertahankan slug bila judul tidak berubah', function () {
    $portofolio = Portofolio::factory()->create(['judul' => 'Portofolio Uji', 'slug' => 'slug-lama']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['gambar' => null, 'kategori' => 'Mobile Apps']));

    expect($portofolio->refresh()->slug)->toBe('slug-lama')
        ->and($portofolio->kategori)->toBe('Mobile Apps');
});

it('memperbarui slug tanpa bentrok dengan portofolio lain saat judul berubah', function () {
    Portofolio::factory()->create(['slug' => 'judul-baru']);
    $portofolio = Portofolio::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['judul' => 'Judul Baru', 'gambar' => null]));

    expect($portofolio->refresh()->slug)->toBe('judul-baru-2');
});

it('mengganti gambar dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/portofolio/lama.webp', 'isi');
    $portofolio = Portofolio::factory()->create(['gambar' => 'company-profile/portofolio/lama.webp']);

    $this->portofolio->perbarui($portofolio, dataPortofolioUji(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($portofolio->refresh()->gambar)->toEndWith('.jpg');
    Storage::disk('public')->assertMissing('company-profile/portofolio/lama.webp');
});

it('membersihkan gambar baru bila penyimpanan gagal', function () {
    // Kategori melebihi VARCHAR(50) sehingga INSERT gagal di MySQL.
    expect(fn () => $this->portofolio->simpan(dataPortofolioUji(['kategori' => str_repeat('a', 100)])))
        ->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles(Portofolio::FOLDER))->toBe([]);
});
