<?php

use App\Models\Pengguna;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Schema;

it('menampilkan halaman login untuk tamu', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke Panel Admin')
        ->assertDontSee('remember');
});

it('mengarahkan pengguna yang sudah login dari halaman login ke dashboard', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('admin.dashboard'));
});

it('login dengan kredensial benar lalu diarahkan ke dashboard', function () {
    $pengguna = Pengguna::factory()->create();

    $this->post(route('login.store'), [
        'email' => $pengguna->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($pengguna);
});

it('menolak login dengan kata sandi salah', function () {
    $pengguna = Pengguna::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $pengguna->email,
            'password' => 'salah',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

    $this->assertGuest();
});

it('memvalidasi email dan kata sandi wajib diisi', function () {
    $this->post(route('login.store'), [])
        ->assertSessionHasErrors([
            'email' => 'Kolom email wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);
});

it('menolak login pengguna yang sudah dihapus', function () {
    $pengguna = Pengguna::factory()->create();
    $pengguna->delete();

    $this->post(route('login.store'), [
        'email' => $pengguna->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('membatasi 5 percobaan login gagal per menit', function () {
    $pengguna = Pengguna::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), ['email' => $pengguna->email, 'password' => 'salah']);
    }

    // Percobaan ke-6 tetap ditolak walau kata sandi benar.
    $this->post(route('login.store'), ['email' => $pengguna->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan masuk');
});

it('tidak membatasi percobaan login saat APP_ENV=local', function () {
    app()->detectEnvironment(fn () => 'local');
    // Di luar env testing, CSRF tidak lagi dilewati otomatis.
    $this->withoutMiddleware(PreventRequestForgery::class);
    $pengguna = Pengguna::factory()->create();

    for ($i = 0; $i < 6; $i++) {
        $this->post(route('login.store'), ['email' => $pengguna->email, 'password' => 'salah']);
    }

    $this->post(route('login.store'), ['email' => $pengguna->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($pengguna);
});

it('logout mengakhiri sesi dan kembali ke halaman login', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('tidak menyimpan remember token saat login', function () {
    $pengguna = Pengguna::factory()->create();

    $this->post(route('login.store'), ['email' => $pengguna->email, 'password' => 'password']);

    expect($pengguna->getRememberTokenName())->toBe('')
        ->and(Schema::hasColumn('tb_pengguna', 'remember_token'))->toBeFalse();
});
