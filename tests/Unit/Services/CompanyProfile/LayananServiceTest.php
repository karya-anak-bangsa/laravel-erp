<?php

use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\LayananService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->layanan = app(LayananService::class);
});

function dataLayananLayanan(array $timpa = []): array
{
    return [
        'judul' => 'Layanan Uji',
        'deskripsi' => 'Deskripsi layanan uji.',
        'keterangan' => null,
        'gambar' => UploadedFile::fake()->image('layanan.webp'),
        'urutan_ke' => 1,
        ...$timpa,
    ];
}

it('menyimpan layanan baru beserta gambarnya', function () {
    $layanan = $this->layanan->simpan(dataLayananLayanan());

    expect($layanan->exists)->toBeTrue()
        ->and($layanan->gambar)->toStartWith('company-profile/layanan/');
    Storage::disk('public')->assertExists($layanan->gambar);
});

it('mempertahankan gambar lama bila tidak ada gambar baru', function () {
    $layanan = Layanan::factory()->create(['gambar' => 'company-profile/layanan/lama.webp']);

    $this->layanan->perbarui($layanan, dataLayananLayanan(['gambar' => null, 'judul' => 'Judul Baru']));

    expect($layanan->refresh()->judul)->toBe('Judul Baru')
        ->and($layanan->gambar)->toBe('company-profile/layanan/lama.webp');
});

it('mengganti gambar dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/layanan/lama.webp', 'isi');
    $layanan = Layanan::factory()->create(['gambar' => 'company-profile/layanan/lama.webp']);

    $this->layanan->perbarui($layanan, dataLayananLayanan(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($layanan->refresh()->gambar)->toEndWith('.jpg');
    Storage::disk('public')->assertMissing('company-profile/layanan/lama.webp');
});

it('membersihkan gambar baru bila penyimpanan gagal', function () {
    // Judul melebihi VARCHAR(150) sehingga INSERT gagal di MySQL.
    expect(fn () => $this->layanan->simpan(dataLayananLayanan(['judul' => str_repeat('a', 300)])))
        ->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles(Layanan::FOLDER))->toBe([]);
});
