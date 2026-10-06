<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

it('memakai bahasa Indonesia dan zona waktu Asia/Jakarta', function () {
    expect(config('app.locale'))->toBe('id')
        ->and(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(date_default_timezone_get())->toBe('Asia/Jakarta');
});

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $validator = Validator::make(['nama_akun' => ''], ['nama_akun' => 'required']);

    expect($validator->errors()->first('nama_akun'))->toBe('Kolom nama akun wajib diisi.');
});

it('terhubung ke database MySQL', function () {
    expect(DB::connection()->getDriverName())->toBe('mysql');
});

it('mengarahkan halaman utama ke panel admin', function () {
    $this->get('/')->assertRedirect('/admin');
});
