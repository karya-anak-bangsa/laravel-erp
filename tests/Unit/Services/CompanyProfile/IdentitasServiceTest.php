<?php

use App\Models\CompanyProfile\Identitas;
use App\Services\CompanyProfile\IdentitasService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->layanan = app(IdentitasService::class);

    Storage::disk('public')->put('company-profile/identitas/logo-lama.png', 'isi');
    Storage::disk('public')->put('company-profile/identitas/favicon-lama.png', 'isi');
    $this->identitas = Identitas::factory()->create([
        'logo_website' => 'company-profile/identitas/logo-lama.png',
        'favicon_website' => 'company-profile/identitas/favicon-lama.png',
    ]);
});

it('mempertahankan berkas lama bila tidak ada berkas baru', function () {
    $this->layanan->perbarui($this->identitas, ['nama_perusahaan' => 'PT Baru', 'logo_website' => null]);

    expect($this->identitas->refresh()->nama_perusahaan)->toBe('PT Baru')
        ->and($this->identitas->logo_website)->toBe('company-profile/identitas/logo-lama.png');
    Storage::disk('public')->assertExists(['company-profile/identitas/logo-lama.png', 'company-profile/identitas/favicon-lama.png']);
});

it('hanya mengganti berkas yang diunggah', function () {
    $this->layanan->perbarui($this->identitas, ['logo_website' => UploadedFile::fake()->image('logo.webp')]);

    $this->identitas->refresh();
    expect($this->identitas->logo_website)->toMatch('#^company-profile/identitas/[0-9a-f-]{36}\.webp$#')
        ->and($this->identitas->favicon_website)->toBe('company-profile/identitas/favicon-lama.png');
    Storage::disk('public')->assertMissing('company-profile/identitas/logo-lama.png');
    Storage::disk('public')->assertExists(['company-profile/identitas/favicon-lama.png', $this->identitas->logo_website]);
});

it('membersihkan berkas baru dan mempertahankan berkas lama bila penyimpanan gagal', function () {
    // Nama perusahaan melebihi VARCHAR(150) sehingga query UPDATE gagal di MySQL.
    expect(fn () => $this->layanan->perbarui($this->identitas, [
        'nama_perusahaan' => str_repeat('a', 300),
        'logo_website' => UploadedFile::fake()->image('logo.png'),
    ]))->toThrow(QueryException::class);

    expect(Storage::disk('public')->allFiles('company-profile/identitas'))->toEqualCanonicalizing([
        'company-profile/identitas/logo-lama.png',
        'company-profile/identitas/favicon-lama.png',
    ]);
    expect($this->identitas->fresh()->logo_website)->toBe('company-profile/identitas/logo-lama.png');
});
