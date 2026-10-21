<?php

use App\Enums\Sistem\PeranPengguna;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create(['nama' => 'Budi Santoso']);
});

it('mengarahkan tamu ke halaman login', function () {
    $pengguna = Pengguna::factory()->create();
    $data = ['nama' => 'Ani', 'email' => 'ani@example.com', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'];

    $this->get(route('admin.pengguna.index'))->assertRedirect(route('login'));
    $this->get(route('admin.pengguna.create'))->assertRedirect(route('login'));
    $this->post(route('admin.pengguna.store'), $data)->assertRedirect(route('login'));
    $this->get(route('admin.pengguna.edit', $pengguna))->assertRedirect(route('login'));
    $this->put(route('admin.pengguna.update', $pengguna), $data)->assertRedirect(route('login'));
    $this->delete(route('admin.pengguna.destroy', $pengguna))->assertRedirect(route('login'));

    expect(Pengguna::count())->toBe(2);
});

it('menampilkan daftar pengguna urut abjad sesuai standar admin', function () {
    $citra = Pengguna::factory()->administrator()->create(['nama' => 'Citra Lestari', 'created_at' => '2026-10-09 08:30:00']);
    Pengguna::factory()->create(['nama' => 'Agus Salim']);

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.index'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSee('class="nav-link active" href="'.route('admin.pengguna.index').'"', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Manajemen Pengguna</div>',
            'href="'.route('admin.pengguna.create').'"',
            '<table class="table">', '<th class="kolom-pas">Peran</th>',
            'Agus Salim', 'Super Administrator', 'Budi Santoso', 'Super Administrator',
            'Citra Lestari', '<td class="kolom-pas">Administrator</td>', $citra->email,
            'data-detail-title="Detail Pengguna"', '<th scope="row">Peran</th>', '<td>Administrator</td>', '09 Oktober 2026, 08:30',
            'href="'.route('admin.pengguna.edit', $citra).'"',
            'action="'.route('admin.pengguna.destroy', $citra).'"',
        ], false);
});

it('menandai akun sendiri dan menonaktifkan tombol hapusnya', function () {
    $lain = Pengguna::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.index'))
        ->assertSeeInOrder(['Budi Santoso</span>', '(Anda)', 'title="Akun yang sedang dipakai tidak bisa dihapus"'], false)
        ->assertDontSee('action="'.route('admin.pengguna.destroy', $this->admin).'"', false)
        ->assertSee('action="'.route('admin.pengguna.destroy', $lain).'"', false);
});

it('mencari pengguna berdasarkan nama atau email', function () {
    Pengguna::factory()->create(['nama' => 'Dewi Anggraini', 'email' => 'dewi@example.com']);
    Pengguna::factory()->create(['nama' => 'Eko Prasetyo', 'email' => 'eko@karyaanakbangsa.co.id']);

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.index', ['q' => 'dewi']))
        ->assertSee('Dewi Anggraini')
        ->assertDontSee('Eko Prasetyo');

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.index', ['q' => 'karyaanakbangsa']))
        ->assertSee('Eko Prasetyo')
        ->assertDontSee('Dewi Anggraini');

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.index', ['q' => 'tidak-ada']))
        ->assertSee('Pengguna tidak ditemukan');
});

it('menambah pengguna dengan kata sandi ter-hash', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.create'))
        ->assertOk()
        // Peran wajib dipilih sendiri, tanpa nilai terpilih bawaan.
        ->assertSeeInOrder([
            'name="peran"', '<option value="">— Pilih —</option>',
            '<option value="super_administrator" >Super Administrator</option>', '<option value="administrator" >Administrator</option>',
        ], false)
        // Browser tidak boleh mengisi kata sandi admin yang sedang login.
        ->assertSeeInOrder(['name="password"', 'autocomplete="new-password"', 'name="password_confirmation"', 'autocomplete="new-password"'], false);

    $this->actingAs($this->admin)
        ->post(route('admin.pengguna.store'), [
            'nama' => 'Fajar Nugroho',
            'email' => 'fajar@example.com',
            'peran' => 'administrator',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertRedirect(route('admin.pengguna.index'))
        ->assertSessionHas('success');

    $pengguna = Pengguna::where('email', 'fajar@example.com')->sole();

    expect($pengguna->nama)->toBe('Fajar Nugroho')
        ->and($pengguna->peran)->toBe(PeranPengguna::Administrator)
        ->and($pengguna->password)->not->toBe('rahasia123')
        ->and(Hash::check('rahasia123', $pengguna->password))->toBeTrue();
});

it('memvalidasi isian pengguna baru', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pengguna.store'), [])
        ->assertSessionHasErrors([
            'nama' => 'Kolom nama wajib diisi.',
            'email' => 'Kolom email wajib diisi.',
            'peran' => 'Kolom peran wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);

    $this->actingAs($this->admin)
        ->post(route('admin.pengguna.store'), [
            'nama' => str_repeat('a', 101),
            'email' => 'bukan-email',
            'peran' => 'pemilik',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])
        ->assertSessionHasErrors(['nama', 'email', 'peran', 'password' => 'Kolom kata sandi harus berisi minimal 8 karakter.']);

    $this->actingAs($this->admin)
        ->post(route('admin.pengguna.store'), [
            'nama' => 'Gita',
            'email' => 'gita@example.com',
            'peran' => 'administrator',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia321',
        ])
        ->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak cocok.']);

    expect(Pengguna::count())->toBe(1);
});

it('menolak email yang sudah dipakai, termasuk oleh akun yang sudah dihapus', function () {
    Pengguna::factory()->create(['email' => 'aktif@example.com']);
    Pengguna::factory()->create(['email' => 'terhapus@example.com'])->delete();

    foreach (['aktif@example.com', 'TERHAPUS@example.com'] as $email) {
        $this->actingAs($this->admin)
            ->post(route('admin.pengguna.store'), [
                'nama' => 'Hadi',
                'email' => $email,
                'peran' => 'administrator',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors(['email' => 'Email sudah dipakai akun lain, termasuk akun yang sudah dihapus.']);
    }
});

it('mengubah pengguna tanpa mengganti kata sandi bila isiannya kosong', function () {
    $pengguna = Pengguna::factory()->administrator()->create(['email' => 'indah@example.com', 'password' => 'rahasia123']);

    $this->actingAs($this->admin)
        ->get(route('admin.pengguna.edit', $pengguna))
        ->assertOk()
        ->assertSee('value="indah@example.com"', false)
        ->assertSee('<option value="administrator" selected>Administrator</option>', false)
        ->assertSee('Kosongkan bila tidak ingin mengganti.');

    // Email sendiri tidak dianggap bentrok.
    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $pengguna), [
            'nama' => 'Indah Permata',
            'email' => 'indah@example.com',
            'peran' => 'super_administrator',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.pengguna.index'))
        ->assertSessionHas('success');

    expect($pengguna->refresh()->nama)->toBe('Indah Permata')
        ->and($pengguna->peran)->toBe(PeranPengguna::SuperAdministrator)
        ->and(Hash::check('rahasia123', $pengguna->password))->toBeTrue();
});

it('mengganti kata sandi pengguna bila diisi', function () {
    $pengguna = Pengguna::factory()->create(['password' => 'rahasia123']);
    $data = ['nama' => $pengguna->nama, 'email' => $pengguna->email, 'peran' => $pengguna->peran->value];

    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $pengguna), [...$data, 'password' => 'baru1234', 'password_confirmation' => 'beda1234'])
        ->assertSessionHasErrors(['password' => 'Konfirmasi kata sandi tidak cocok.']);

    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $pengguna), [...$data, 'password' => 'baru1234', 'password_confirmation' => 'baru1234'])
        ->assertSessionHasNoErrors();

    expect(Hash::check('baru1234', $pengguna->refresh()->password))->toBeTrue();
});

it('mengakhiri sesi pengguna yang kata sandinya diganti admin lain', function () {
    $lain = Pengguna::factory()->create(['password' => 'rahasia123']);
    $this->post(route('login.store'), ['email' => $lain->email, 'password' => 'rahasia123']);
    $sesiLain = session()->all();

    auth()->forgetGuards();
    $this->flushSession()
        ->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $lain), [
            'nama' => $lain->nama, 'email' => $lain->email, 'peran' => 'administrator',
            'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])
        ->assertSessionHasNoErrors();

    auth()->forgetGuards();
    $this->flushSession()
        ->withSession($sesiLain)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('menghapus pengguna lain dan mengeluarkannya dari sesinya', function () {
    $lain = Pengguna::factory()->create();
    $kunciSesi = auth()->guard()->getName();

    // Kontrol: sesi yang menyimpan id akun aktif tetap masuk.
    $this->withSession([$kunciSesi => $lain->id_pengguna])->get(route('admin.dashboard'))->assertOk();

    auth()->forgetGuards();
    $this->actingAs($this->admin)
        ->delete(route('admin.pengguna.destroy', $lain))
        ->assertRedirect(route('admin.pengguna.index'))
        ->assertSessionHas('success');

    expect($lain->refresh()->trashed())->toBeTrue();

    // Soft delete membuat sesi akun itu tidak lagi menemukan akunnya.
    auth()->forgetGuards();
    $this->withSession([$kunciSesi => $lain->id_pengguna])->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('menolak menghapus akun yang sedang dipakai', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.pengguna.destroy', $this->admin))
        ->assertRedirect(route('admin.pengguna.index'))
        ->assertSessionHas('error', 'Akun yang sedang Anda pakai tidak bisa dihapus.');

    expect($this->admin->refresh()->trashed())->toBeFalse();
});
