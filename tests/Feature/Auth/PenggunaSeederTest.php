<?php

use App\Models\Pengguna;
use Database\Seeders\PenggunaSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config()->set('auth.admin_awal', [
        'nama' => 'Admin Uji',
        'email' => 'admin@contoh.test',
        'password' => 'rahasia-uji',
    ]);
});

it('membuat akun admin dari konfigurasi env', function () {
    $this->seed(PenggunaSeeder::class);

    $admin = Pengguna::where('email', 'admin@contoh.test')->firstOrFail();

    expect($admin->nama)->toBe('Admin Uji')
        ->and(Hash::check('rahasia-uji', $admin->password))->toBeTrue();
});

it('aman dijalankan ulang tanpa menimpa kata sandi', function () {
    $this->seed(PenggunaSeeder::class);
    Pengguna::where('email', 'admin@contoh.test')->update(['password' => Hash::make('sandi-baru')]);

    $this->seed(PenggunaSeeder::class);

    expect(Pengguna::count())->toBe(1)
        ->and(Hash::check('sandi-baru', Pengguna::first()->password))->toBeTrue();
});

it('gagal bila variabel admin belum diisi', function () {
    config()->set('auth.admin_awal.email', null);

    $this->seed(PenggunaSeeder::class);
})->throws(RuntimeException::class, 'wajib diisi');
