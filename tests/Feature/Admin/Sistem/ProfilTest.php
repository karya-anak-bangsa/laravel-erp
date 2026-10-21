<?php

use App\Enums\Sistem\PeranPengguna;
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
            'name="password"', 'autocomplete="new-password"', 'data-toggle-password="password"',
            'name="password_confirmation"', 'autocomplete="new-password"', 'data-toggle-password="password_confirmation"',
            'href="'.route('admin.dashboard').'" class="btn btn-secondary"',
        ], false)
        ->assertDontSee('Kata Sandi Saat Ini');
});

it('mengubah nama dan email tanpa kata sandi saat ini lalu kembali ke dashboard', function () {
    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['nama' => 'Budi S.', 'email' => 'budi.baru@example.com']))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('success', 'Profil berhasil diperbarui.');

    expect($this->pengguna->refresh()->nama)->toBe('Budi S.')
        ->and($this->pengguna->email)->toBe('budi.baru@example.com')
        ->and(Hash::check('rahasia123', $this->pengguna->password))->toBeTrue();
});

it('menolak email milik akun lain, termasuk akun yang sudah dihapus', function () {
    Pengguna::factory()->create(['email' => 'terhapus@example.com'])->delete();

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'terhapus@example.com']))
        ->assertSessionHasErrors(['email' => 'Email sudah dipakai akun lain, termasuk akun yang sudah dihapus.']);
});

it('mengganti kata sandi cukup dengan kata sandi baru dan konfirmasinya', function () {
    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['password' => 'pendek', 'password_confirmation' => 'pendek']))
        ->assertSessionHasErrors(['password' => 'Kolom kata sandi baru harus berisi minimal 8 karakter.']);

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['password' => 'baru12345', 'password_confirmation' => 'baru54321']))
        ->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi baru tidak cocok.']);

    expect(Hash::check('rahasia123', $this->pengguna->refresh()->password))->toBeTrue();

    $this->actingAs($this->pengguna)
        ->put(route('admin.profil.update'), dataProfilValid(['password' => 'baru12345', 'password_confirmation' => 'baru12345']))
        ->assertSessionHasNoErrors();

    expect(Hash::check('baru12345', $this->pengguna->refresh()->password))->toBeTrue();
});

it('memutus sesi di perangkat lain saat kata sandi diganti, tetapi tidak sesi yang menggantinya', function () {
    $this->post(route('login.store'), ['email' => 'budi@example.com', 'password' => 'rahasia123']);
    $sesiPerangkatLain = session()->all();

    $this->put(route('admin.profil.update'), dataProfilValid(['password' => 'baru12345', 'password_confirmation' => 'baru12345']))
        ->assertSessionHasNoErrors();

    // Akun dibaca ulang dari database seperti request sungguhan berikutnya.
    auth()->forgetGuards();
    $this->get(route('admin.dashboard'))->assertOk();

    auth()->forgetGuards();
    $this->flushSession()
        ->withSession($sesiPerangkatLain)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('tidak menyediakan pengubahan peran sendiri lewat Profil Saya', function () {
    $administrator = Pengguna::factory()->administrator()->create(['email' => 'ani@example.com']);

    $this->actingAs($administrator)
        ->get(route('admin.profil.edit'))
        ->assertOk()
        ->assertDontSee('name="peran"', false);

    $this->actingAs($administrator)
        ->put(route('admin.profil.update'), dataProfilValid(['email' => 'ani@example.com', 'peran' => 'super_administrator']))
        ->assertSessionHasNoErrors();

    expect($administrator->refresh()->peran)->toBe(PeranPengguna::Administrator);
});
