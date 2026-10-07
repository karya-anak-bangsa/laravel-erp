<?php

use App\Services\Shared\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->layanan = new FileUploadService;
});

it('menyimpan berkas dengan nama UUID dan ekstensi sesuai isi berkas', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->create('nota asli.pdf', 10, 'application/pdf'), 'kas/bukti', 'local');

    expect($path)->toMatch('#^kas/bukti/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($path);
});

it('menghapus berkas yang ada', function () {
    Storage::disk('local')->put('kas/bukti/lama.pdf', 'isi');

    $this->layanan->hapus('kas/bukti/lama.pdf', 'local');

    Storage::disk('local')->assertMissing('kas/bukti/lama.pdf');
});

it('mengabaikan path kosong saat menghapus', function () {
    Storage::disk('local')->put('kas/bukti/tetap.pdf', 'isi');

    $this->layanan->hapus(null, 'local');
    $this->layanan->hapus('', 'local');

    Storage::disk('local')->assertExists('kas/bukti/tetap.pdf');
});
