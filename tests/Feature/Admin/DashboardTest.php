<?php

use App\Models\Pengguna;

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('menampilkan dashboard untuk pengguna yang login', function () {
    $pengguna = Pengguna::factory()->create(['nama' => 'Aryajaya Alamsyah']);

    $this->actingAs($pengguna)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Selamat datang, Aryajaya Alamsyah')
        ->assertSee('Belum ada ringkasan')
        ->assertSee(route('logout'));
});

it('menampilkan menu pengguna berisi logout di sidebar, bukan di topbar', function () {
    $pengguna = Pengguna::factory()->create(['nama' => 'Aryajaya Alamsyah', 'email' => 'admin@karyaanakbangsa.co.id']);

    $this->actingAs($pengguna)
        ->get(route('admin.dashboard'))
        ->assertSee('class="sidebar-user" type="button" data-menu="menu-pengguna"', false)
        ->assertDontSee('tb-user', false)
        ->assertDontSee('topbar-right', false)
        ->assertSee('<template id="menu-pengguna">', false)
        ->assertSee('admin@karyaanakbangsa.co.id')
        ->assertSee('<form method="POST" action="'.route('logout').'">', false)
        ->assertDontSee('theme-toggle', false);
});

it('tidak menyediakan mode gelap', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertDontSee('data-aksi="ganti-tema"', false)
        ->assertDontSee('data-theme', false);
});

it('memakai ikon landmark untuk menu dashboard', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('fa-landmark', false);
});

it('menandai menu dashboard sebagai menu aktif', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('class="nav-link active"', false);
});

it('menampilkan nama sistem di brand sidebar dan footer', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('<div class="brand-name">ERP System</div>', false)
        ->assertSee('<span>ERP System v1.0</span>', false);
});

it('menyusun menu sidebar: artikel sebagai submenu dan identitas di pengaturan sistem', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSeeInOrder([
            'Company Profile</div>',
            'Hero', 'Layanan', 'Portofolio',
            // Submenu tertutup karena halaman aktif (dashboard) tidak ada di dalamnya.
            '<div class="nav-tree">',
            '<button type="button" class="nav-link nav-toggle" aria-expanded="false">',
            '<span class="nav-text">Artikel</span>',
            'class="nav-sublink" href="'.route('admin.artikel.index').'" >Daftar Artikel</a>',
            'class="nav-sublink" href="'.route('admin.kategori-artikel.index').'" >Kategori Artikel</a>',
            'FAQ', 'Kontak Kami',
            'Pengaturan Sistem</div>',
            'href="'.route('admin.identitas.edit').'"',
        ], false);
});

it('menandai menu aktif memakai pola rute pada kunci aktif', function () {
    // Menu modul aktif di semua halamannya (index, create, edit) lewat pola seperti admin.artikel.*.
    config(['menu' => [[
        'judul' => 'Uji',
        'item' => [['label' => 'Menu Uji', 'ikon' => 'gear', 'rute' => 'login', 'aktif' => 'admin.*']],
    ]]]);

    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('class="nav-link active" href="'.route('login').'"', false);
});
