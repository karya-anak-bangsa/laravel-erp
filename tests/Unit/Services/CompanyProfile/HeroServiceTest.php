<?php

use App\Models\CompanyProfile\Hero;
use App\Services\CompanyProfile\HeroService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->layanan = app(HeroService::class);
});

function dataHeroLayanan(array $timpa = []): array
{
    return [
        'judul' => 'Hero Uji',
        'deskripsi' => 'Deskripsi hero uji.',
        'gambar' => UploadedFile::fake()->image('hero.webp'),
        'keyword' => ['Website'],
        'cta' => [],
        'status_aktif' => true,
        ...$timpa,
    ];
}

it('menyimpan hero baru beserta gambarnya', function () {
    $hero = $this->layanan->simpan(dataHeroLayanan());

    expect($hero->exists)->toBeTrue()
        ->and($hero->gambar)->toStartWith('company-profile/hero/');
    Storage::disk('public')->assertExists($hero->gambar);
});

it('hanya menyisakan satu hero aktif, termasuk terhadap hero yang terhapus', function () {
    $aktif = Hero::factory()->aktif()->create();
    $terhapus = Hero::factory()->aktif()->create();
    $terhapus->delete();

    $baru = $this->layanan->simpan(dataHeroLayanan());

    expect($baru->status_aktif)->toBeTrue()
        ->and($aktif->refresh()->status_aktif)->toBeFalse()
        ->and(Hero::withTrashed()->find($terhapus->id_hero)->status_aktif)->toBeFalse();
});

it('tidak mengubah hero aktif lain saat hero disimpan nonaktif', function () {
    $aktif = Hero::factory()->aktif()->create();

    $this->layanan->simpan(dataHeroLayanan(['status_aktif' => false]));

    expect($aktif->refresh()->status_aktif)->toBeTrue();
});

it('mengganti gambar saat memperbarui dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/hero/lama.webp', 'isi');
    $hero = Hero::factory()->create(['gambar' => 'company-profile/hero/lama.webp']);

    $this->layanan->perbarui($hero, dataHeroLayanan(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($hero->refresh()->gambar)->toEndWith('.jpg');
    Storage::disk('public')->assertMissing('company-profile/hero/lama.webp');
});

it('membersihkan gambar baru dan membatalkan penonaktifan bila penyimpanan gagal', function () {
    $aktif = Hero::factory()->aktif()->create();

    // Judul melebihi VARCHAR(200) sehingga INSERT gagal di MySQL.
    expect(fn () => $this->layanan->simpan(dataHeroLayanan(['judul' => str_repeat('a', 300)])))
        ->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles('company-profile/hero'))->toBe([])
        ->and($aktif->refresh()->status_aktif)->toBeTrue();
});
