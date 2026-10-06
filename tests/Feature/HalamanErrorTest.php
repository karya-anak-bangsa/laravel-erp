<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Detail error disembunyikan seperti di produksi agar halaman kustom yang tampil.
    config()->set('app.debug', false);
});

it('menampilkan halaman 404 kustom', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan');
});

it('menampilkan halaman error kustom', function (int $kode, string $judul) {
    Route::middleware('web')->get('/uji-error', fn () => abort($kode));

    $this->get('/uji-error')
        ->assertStatus($kode)
        ->assertSee($judul);
})->with([
    [403, 'Akses ditolak'],
    [419, 'Sesi telah berakhir'],
    [503, 'Sedang dalam pemeliharaan'],
]);

it('menampilkan halaman 500 tanpa detail error', function () {
    Route::middleware('web')->get('/uji-error', fn () => throw new RuntimeException('detail-rahasia'));

    $this->get('/uji-error')
        ->assertStatus(500)
        ->assertSee('Terjadi kesalahan server')
        ->assertDontSee('detail-rahasia');
});
