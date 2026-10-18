<?php

use App\Services\Shared\FileUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->layanan = new FileUploadService;
});

it('menyimpan berkas dengan nama UUID dan ekstensi sesuai isi berkas', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->create('dokumen asli.pdf', 10, 'application/pdf'), 'uji/dokumen', 'local');

    expect($path)->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($path);
});

it('menghapus berkas yang ada', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');

    $this->layanan->hapus('uji/dokumen/lama.pdf', 'local');

    Storage::disk('local')->assertMissing('uji/dokumen/lama.pdf');
});

it('mengabaikan path kosong saat menghapus', function () {
    Storage::disk('local')->put('uji/dokumen/tetap.pdf', 'isi');

    $this->layanan->hapus(null, 'local');
    $this->layanan->hapus('', 'local');

    Storage::disk('local')->assertExists('uji/dokumen/tetap.pdf');
});

it('menjalankan penyimpanan dengan path berkas baru lalu menghapus berkas lama', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');
    $model = new class extends Model {};
    $model->setRawAttributes(['berkas' => 'uji/dokumen/lama.pdf', 'lampiran' => 'uji/dokumen/tetap.pdf']);
    $diterima = null;

    $this->layanan->simpanDenganBerkas($model, [
        'judul' => 'Uji',
        'berkas' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
        'lampiran' => null,
    ], ['berkas', 'lampiran'], 'uji/dokumen', 'local', function (array $data) use ($model, &$diterima) {
        $diterima = $data;

        return $model;
    });

    // Kolom tanpa berkas baru (lampiran) dibuang agar path lamanya tidak tertimpa null.
    expect($diterima)->toHaveKeys(['judul', 'berkas'])->not->toHaveKey('lampiran')
        ->and($diterima['berkas'])->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($diterima['berkas']);
    Storage::disk('local')->assertMissing('uji/dokumen/lama.pdf');
});

it('meneruskan daftar path untuk kolom berisi banyak berkas', function () {
    $diterima = null;

    $this->layanan->simpanDenganBerkas(null, [
        'lampiran' => [
            UploadedFile::fake()->create('satu.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('dua.pdf', 10, 'application/pdf'),
        ],
        'kosong' => [],
    ], ['lampiran', 'kosong'], 'uji/dokumen', 'local', function (array $data) use (&$diterima) {
        $diterima = $data;

        return new class extends Model {};
    });

    // Daftar tanpa berkas dibuang seperti kolom tanpa berkas baru.
    expect($diterima)->toHaveKey('lampiran')->not->toHaveKey('kosong')
        ->and($diterima['lampiran'])->toHaveCount(2)
        ->each->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($diterima['lampiran']);
});

it('menghapus semua berkas dari daftar bila penyimpanan gagal', function () {
    expect(fn () => $this->layanan->simpanDenganBerkas(null, [
        'berkas' => UploadedFile::fake()->create('utama.pdf', 10, 'application/pdf'),
        'lampiran' => [
            UploadedFile::fake()->create('satu.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('dua.pdf', 10, 'application/pdf'),
        ],
    ], ['berkas', 'lampiran'], 'uji/dokumen', 'local', fn () => throw new RuntimeException('gagal')))
        ->toThrow(RuntimeException::class, 'gagal');

    expect(Storage::disk('local')->allFiles('uji/dokumen'))->toBe([]);
});

it('menghapus berkas baru dan mempertahankan berkas lama bila penyimpanan gagal', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');
    $model = new class extends Model {};
    $model->setRawAttributes(['berkas' => 'uji/dokumen/lama.pdf']);

    expect(fn () => $this->layanan->simpanDenganBerkas($model, [
        'berkas' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
    ], ['berkas'], 'uji/dokumen', 'local', fn () => throw new RuntimeException('gagal')))
        ->toThrow(RuntimeException::class, 'gagal');

    expect(Storage::disk('local')->allFiles('uji/dokumen'))->toBe(['uji/dokumen/lama.pdf']);
});
