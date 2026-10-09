<?php

use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KontakKami;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Identitas::factory()->create();

    $this->data = [
        'nama' => 'Budi Santoso',
        'email' => 'budi@contoh.test',
        'subjek' => 'Website company profile',
        'pesan' => 'Saya ingin membuat website untuk usaha saya.',
        'website' => '',
    ];
});

it('menyimpan pesan pengunjung ke kotak masuk sebagai belum dibaca', function () {
    // Kolom tanggal DATETIME tanpa pecahan detik, jadi waktu dibekukan di awal detik.
    $this->freezeSecond();

    $this->post(route('kontak-kami.store'), $this->data)
        ->assertRedirect(KontakKamiController::urlFormulir())
        ->assertSessionHas('kontak_terkirim', KontakKamiController::PESAN_TERKIRIM);

    $pesan = KontakKami::sole();
    expect($pesan->only(['nama', 'email', 'subjek', 'pesan']))->toBe([
        'nama' => 'Budi Santoso',
        'email' => 'budi@contoh.test',
        'subjek' => 'Website company profile',
        'pesan' => 'Saya ingin membuat website untuk usaha saya.',
    ])
        ->and($pesan->status_baca)->toBeFalse()
        ->and($pesan->tanggal->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and(KontakKami::query()->belumDibaca()->count())->toBe(1);
});

it('menampilkan pesan sukses di formulir setelah terkirim', function () {
    $this->followingRedirects()
        ->post(route('kontak-kami.store'), $this->data)
        ->assertOk()
        ->assertSeeInOrder(['<div role="status" class="sm:order-1">', KontakKamiController::PESAN_TERKIRIM], false);
});

it('memvalidasi isian dengan pesan berbahasa Indonesia dan kembali ke formulir', function (array $isian, string $kolom, string $pesan) {
    $this->post(route('kontak-kami.store'), [...$this->data, ...$isian])
        ->assertRedirect(KontakKamiController::urlFormulir())
        ->assertSessionHasErrors([$kolom => $pesan]);

    expect(KontakKami::count())->toBe(0);
})->with([
    'nama kosong' => [['nama' => ''], 'nama', 'Kolom nama wajib diisi.'],
    'email kosong' => [['email' => ''], 'email', 'Kolom email wajib diisi.'],
    'email tidak valid' => [['email' => 'bukan-email'], 'email', 'Kolom email harus berupa alamat email yang valid.'],
    'subjek kosong' => [['subjek' => ''], 'subjek', 'Kolom subjek wajib diisi.'],
    'pesan kosong' => [['pesan' => ''], 'pesan', 'Kolom pesan wajib diisi.'],
    'nama terlalu panjang' => [['nama' => str_repeat('a', 101)], 'nama', 'Kolom nama tidak boleh lebih dari 100 karakter.'],
    'pesan terlalu panjang' => [['pesan' => str_repeat('a', 5001)], 'pesan', 'Kolom pesan tidak boleh lebih dari 5000 karakter.'],
]);

it('menampilkan galat dan isian lama di formulir setelah validasi gagal', function () {
    $html = $this->followingRedirects()
        ->post(route('kontak-kami.store'), [...$this->data, 'email' => 'bukan-email'])
        ->assertSee('value="Budi Santoso"', false)
        ->assertSee('<p id="kontak-email-galat" class="galat-isian">Kolom email harus berupa alamat email yang valid.</p>', false)
        ->getContent();

    expect($html)->toMatch('#id="kontak-email"[^>]*aria-invalid="true"\s+aria-describedby="kontak-email-galat"#')
        ->toMatch('#id="kontak-nama"[^>]*aria-invalid="false"#');
});

it('tidak menyimpan pesan bot yang mengisi honeypot tetapi tetap membalas sukses', function () {
    $this->post(route('kontak-kami.store'), [...$this->data, 'website' => 'https://spam.test'])
        ->assertRedirect(KontakKamiController::urlFormulir())
        ->assertSessionHas('kontak_terkirim');

    expect(KontakKami::count())->toBe(0);
});

it('membatasi tiga kiriman per menit dari IP yang sama', function () {
    for ($kiriman = 1; $kiriman <= 3; $kiriman++) {
        $this->post(route('kontak-kami.store'), $this->data)->assertSessionHasNoErrors();
    }

    $this->post(route('kontak-kami.store'), [...$this->data, 'nama' => 'Kiriman Keempat'])
        ->assertRedirect(KontakKamiController::urlFormulir())
        ->assertSessionHasErrors(['formulir' => KontakKamiController::PESAN_DIBATASI])
        ->assertSessionHasInput('nama', 'Kiriman Keempat');

    expect(KontakKami::count())->toBe(3);
});

it('mengembalikan pengunjung ke formulir dengan isian utuh bila sesi sudah berakhir', function () {
    // Token CSRF hanya diperiksa di luar lingkungan testing.
    $this->app['env'] = 'local';

    $this->post(route('kontak-kami.store'), $this->data)
        ->assertRedirect(KontakKamiController::urlFormulir())
        ->assertSessionHasErrors(['formulir' => KontakKamiController::PESAN_SESI_HABIS])
        ->assertSessionHasInput('pesan', $this->data['pesan']);

    expect(KontakKami::count())->toBe(0);
});

it('mengarahkan GET /kontak ke formulir di beranda', function () {
    $this->get('/kontak')->assertRedirect('/#kontak');
});
