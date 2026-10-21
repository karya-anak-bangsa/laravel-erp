<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Template;
use App\Models\Pengguna;

beforeEach(function () {
    $this->administrator = Pengguna::factory()->administrator()->create();
});

it('menjaga setiap menu sidebar sesuai izin grupnya', function () {
    // Menu grup berizin (Pengaturan Sistem) ditolak, selebihnya terbuka bagi Administrator.
    foreach (config('menu') as $grup) {
        foreach ($grup['item'] as $item) {
            foreach ($item['sub'] ?? [$item] as $menu) {
                $this->actingAs($this->administrator)
                    ->get(route($menu['rute']))
                    ->assertStatus(isset($grup['izin']) ? 403 : 200);
            }
        }
    }

    $this->actingAs($this->administrator)->get(route('admin.profil.edit'))->assertOk();
});

it('menyembunyikan grup Pengaturan Sistem dari sidebar Administrator', function () {
    $this->actingAs($this->administrator)
        ->get(route('admin.dashboard'))
        ->assertSee('Company Profile</div>', false)
        ->assertDontSee('Pengaturan Sistem</div>', false)
        ->assertDontSee('href="'.route('admin.identitas.edit').'"', false)
        ->assertDontSee('href="'.route('admin.pengguna.index').'"', false)
        // Profil Saya tetap ada di menu pengguna.
        ->assertSee('<a href="'.route('admin.profil.edit').'" class="menu-item">', false);

    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('Pengaturan Sistem</div>', false);
});

it('menolak Administrator mengubah data Pengaturan Sistem', function () {
    $modul = ModulWebsite::factory()->create(['jenis' => JenisModul::Faq]);
    $template = Template::factory()->create();
    $lain = Pengguna::factory()->create();
    $nonaktif = Pengguna::factory()->create();
    $nonaktif->delete();

    $permintaan = [
        ['put', route('admin.identitas.update')],
        ['get', route('admin.modul-website.edit', $modul)],
        ['put', route('admin.modul-website.update', $modul)],
        ['get', route('admin.template.create')],
        ['get', route('admin.template.pratinjau')],
        ['post', route('admin.template.store')],
        ['get', route('admin.template.edit', $template)],
        ['put', route('admin.template.update', $template)],
        ['delete', route('admin.template.destroy', $template)],
        ['get', route('admin.pengguna.create')],
        ['post', route('admin.pengguna.store')],
        ['get', route('admin.pengguna.edit', $lain)],
        ['put', route('admin.pengguna.update', $lain)],
        ['delete', route('admin.pengguna.destroy', $lain)],
        ['patch', route('admin.pengguna.aktifkan', $nonaktif)],
    ];

    foreach ($permintaan as [$metode, $alamat]) {
        $this->actingAs($this->administrator)->{$metode}($alamat)->assertForbidden();
    }

    expect($modul->refresh()->trashed())->toBeFalse()
        ->and($template->refresh()->trashed())->toBeFalse()
        ->and($lain->refresh()->trashed())->toBeFalse()
        ->and($nonaktif->refresh()->trashed())->toBeTrue();
});

it('menampilkan peringatan modul nonaktif tanpa tautan bagi Administrator', function () {
    ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Faq]);

    $this->actingAs($this->administrator)
        ->get(route('admin.dashboard'))
        ->assertSee('<span class="chip chip-yellow">1 nonaktif</span>', false)
        ->assertDontSee('href="'.route('admin.modul-website.index').'"', false);
});
