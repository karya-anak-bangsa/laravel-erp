<?php

use App\Services\Shared\FileUploadService;
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
