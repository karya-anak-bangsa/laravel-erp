<?php

use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->pengguna = Pengguna::factory()->create([
        'nama' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
    ]);
});

function dataProfilValid(array $timpa = []): array
{
    return [
        'nama' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password_saat_ini' => '',
        'password' => '',
        'password_confirmation' => '',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.profil.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.profil.update'), dataProfilValid(['nama' => 'Tamu']))->assertRedirect(route('login'));

    expect($this->pengguna->refresh()->nama)->toBe('Budi Santoso');
});

it('menampilkan form profil akun yang sedang login', function () {
    $this->actingAs($this->pengguna)
        ->get(route('admin.profil.edit'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSeeInOrder([
            '<span class="current" aria-current="page">Profil Saya</span>',
            'value="Budi Santoso"', 'value="budi@example.com"',
            'name="password_saat_ini"', 'autocomplete="current-password"',
            'name="password"', 'autocomplete="new-password"',
            'name="password_confirmation"', 'autocomplete="new-password"',
        ], false);
});

it('mengubah nama tanpa kata sandi saat ini', function () {
    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['nama' => 'Budi S.']))
        ->assertRedirect(route('admin.profil.edit'))
        ->assertSessionHas('success', 'Profil berhasil diperbarui.');

    expect($this->pengguna->refresh()->nama)->toBe('Budi S.')
        ->and(Hash::check('rahasia123', $this->pengguna->password))->toBeTrue();
});

it('meminta kata sandi saat ini yang benar untuk mengganti email', function () {
    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'budi.baru@example.com']))
        ->assertSessionHasErrors(['password_saat_ini' => 'Masukkan kata sandi saat ini untuk mengganti email atau kata sandi.']);

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'budi.baru@example.com', 'password_saat_ini' => 'salah123']))
        ->assertSessionHasErrors(['password_saat_ini' => 'Kata sandi saat ini salah.']);

    expect($this->pengguna->refresh()->email)->toBe('budi@example.com');

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'budi.baru@example.com', 'password_saat_ini' => 'rahasia123']))
        ->assertSessionHasNoErrors();

    expect($this->pengguna->refresh()->email)->toBe('budi.baru@example.com');
});

it('menolak email milik akun lain, termasuk akun yang sudah dihapus', function () {
    Pengguna::factory()->create(['email' => 'terhapus@example.com'])->delete();

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'terhapus@example.com', 'password_saat_ini' => 'rahasia123']))
        ->assertSessionHasErrors(['email' => 'Email sudah dipakai akun lain, termasuk akun yang sudah dihapus.']);
});

it('mengganti kata sandi dengan kata sandi saat ini dan konfirmasi', function () {
    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['password' => 'baru12345', 'password_confirmation' => 'baru12345']))
        ->assertSessionHasErrors(['password_saat_ini' => 'Masukkan kata sandi saat ini untuk mengganti email atau kata sandi.']);

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid([
            'password_saat_ini' => 'rahasia123', 'password' => 'pendek', 'password_confirmation' => 'beda',
        ]))
        ->assertSessionHasErrors(['password' => 'Kolom kata sandi baru harus berisi minimal 8 karakter.']);

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid([
            'password_saat_ini' => 'rahasia123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ]))
        ->assertSessionHasNoErrors();

    expect(Hash::check('baru12345', $this->pengguna->refresh()->password))->toBeTrue();
});

it('memutus sesi di perangkat lain saat kata sandi diganti, tetapi tidak sesi yang menggantinya', function () {
    $this->post(route('login.store'), ['email' => 'budi@example.com', 'password' => 'rahasia123']);
    $sesiPerangkatLain = session()->all();

    $this->put(route('admin.profil.update'), dataProfilValid([
        'password_saat_ini' => 'rahasia123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
    ]))->assertSessionHasNoErrors();

    // Akun dibaca ulang dari database seperti request sungguhan berikutnya.
    auth()->forgetGuards();
    $this->get(route('admin.dashboard'))->assertOk();

    auth()->forgetGuards();
    $this->flushSession()
        ->withSession($sesiPerangkatLain)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('mengarahkan ubah akun sendiri di Manajemen Pengguna ke Profil Saya', function () {
    $this->actingAs($this->pengguna)
        ->get(route('admin.pengguna.index'))
        ->assertSee('href="'.route('admin.profil.edit').'" class="btn btn-warning btn-ikon"', false)
        ->assertDontSee('href="'.route('admin.pengguna.edit', $this->pengguna).'"', false);

    $this->actingAs($this->pengguna)
        ->get(route('admin.pengguna.edit', $this->pengguna))
        ->assertRedirect(route('admin.profil.edit'));

    // Form CRUD tidak bisa dipakai melewati syarat kata sandi saat ini.
    $this->actingAs($this->pengguna)
        ->put(route('admin.pengguna.update', $this->pengguna), [
            'nama' => 'Budi Santoso', 'email' => 'budi@example.com', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])
        ->assertRedirect(route('admin.profil.edit'));

    expect(Hash::check('rahasia123', $this->pengguna->refresh()->password))->toBeTrue();
});
