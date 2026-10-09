<?php

use App\Models\CompanyProfile\Identitas;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

it('memakai bahasa Indonesia dan zona waktu Asia/Jakarta', function () {
    expect(config('app.locale'))->toBe('id')
        ->and(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(date_default_timezone_get())->toBe('Asia/Jakarta');
});

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $validator = Validator::make(['nama_kategori' => ''], ['nama_kategori' => 'required']);

    expect($validator->errors()->first('nama_kategori'))->toBe('Kolom nama kategori wajib diisi.');
});

it('terhubung ke database MySQL', function () {
    expect(DB::connection()->getDriverName())->toBe('mysql');
});

it('menampilkan beranda publik di halaman utama', function () {
    Storage::fake('public');
    $identitas = Identitas::factory()->create();

    $this->get('/')->assertOk()->assertSee($identitas->nama_perusahaan);
});

it('tidak mengenkripsi cookie tema yang ditulis JavaScript frontend', function () {
    expect(app(EncryptCookies::class)->isDisabled('tema'))->toBeTrue()
        ->and(app(EncryptCookies::class)->isDisabled(config('session.cookie')))->toBeFalse();
});
