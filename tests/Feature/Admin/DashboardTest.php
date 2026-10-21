<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Template;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;
use Illuminate\Support\Str;

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('menampilkan dashboard untuk pengguna yang login dengan page-header seperti modul lain', function () {
    $pengguna = Pengguna::factory()->create(['nama' => 'Aryajaya Alamsyah']);

    $this->actingAs($pengguna)
        ->get('/admin')
        ->assertOk()
        // Hanya judul, tanpa baris sapaan di atasnya (pilihan pemilik 2026-10-20).
        ->assertSee('<h1 class="page-title">Dashboard</h1>', false)
        ->assertDontSee('page-pretitle', false)
        ->assertDontSee('Selamat datang')
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
            'FAQ',
            'Pengaturan Sistem</div>',
            'href="'.route('admin.identitas.edit').'"', 'fa-gear', '<span class="nav-text">Identitas Website</span>',
            'href="'.route('admin.modul-website.index').'"', 'fa-gear',
            'href="'.route('admin.template.index').'"', 'fa-gear',
            'href="'.route('admin.pengguna.index').'"', 'fa-gear', '<span class="nav-text">Manajemen Pengguna</span>',
        ], false)
        // Modul Kontak Kami dihapus 2026-10-20: kontak lewat WhatsApp.
        ->assertDontSee('Kontak Kami');
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

describe('ringkasan dashboard', function () {
    beforeEach(function () {
        $this->admin = Pengguna::factory()->create();
    });

    it('menampilkan kartu angka setiap modul beserta tautan ke daftarnya', function () {
        Artikel::factory()->terbit()->count(2)->create();
        Artikel::factory()->draf()->create();
        Artikel::factory()->terbit()->create()->delete();
        Portofolio::factory()->create();
        Tutorial::factory()->terbit()->has(MateriTutorial::factory()->count(2), 'materiTutorial')->create();
        Tutorial::factory()->draf()->create();
        Layanan::factory()->count(3)->create();
        Faq::factory()->count(4)->create();
        Hero::factory()->aktif()->create();
        Hero::factory()->count(2)->create();
        Hero::factory()->create()->delete();

        // Kategori ikut dibuat factory artikel & portofolio: 4 kategori artikel (termasuk milik artikel terhapus), 1 portofolio.
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder([
                'href="'.route('admin.artikel.index').'" class="card kartu-statistik"', 'Artikel terbit', '<span class="stat-value">2</span>', '1 draf · 4 kategori',
                'href="'.route('admin.portofolio.index').'"', 'Portofolio', '<span class="stat-value">1</span>', '1 kategori',
                'href="'.route('admin.tutorial.index').'"', 'Tutorial terbit', '<span class="stat-value">1</span>', '1 draf · 2 materi',
                'href="'.route('admin.layanan.index').'"', 'Layanan', '<span class="stat-value">3</span>',
                'href="'.route('admin.faq.index').'"', 'FAQ', '<span class="stat-value">4</span>',
                'href="'.route('admin.hero.index').'"', 'Hero', '<span class="stat-value">3</span>', '1 aktif di beranda',
            ], false);
    });

    it('menampilkan konten terakhir diperbarui lintas modul, terbaru lebih dulu, bertautan ke halaman Ubah', function () {
        $faq = Faq::factory()->create(['pertanyaan' => 'FAQ terbaru?', 'updated_at' => '2026-10-20 10:00:00']);
        $artikel = Artikel::factory()->create(['judul' => 'Artikel kemarin', 'updated_at' => '2026-10-19 10:00:00']);
        $hero = Hero::factory()->create(['judul' => 'Hero lama', 'updated_at' => '2026-10-01 10:00:00']);
        Artikel::factory()->create(['judul' => 'Artikel terhapus', 'updated_at' => '2026-10-20 11:00:00'])->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder([
                '<div class="card-title">Terakhir diperbarui</div>',
                'href="'.route('admin.faq.edit', $faq).'" class="judul-ringkas">FAQ terbaru?</a>', 'FAQ · 20 Oktober 2026, 10:00',
                'href="'.route('admin.artikel.edit', $artikel).'" class="judul-ringkas">Artikel kemarin</a>', 'Artikel · 19 Oktober 2026, 10:00',
                'href="'.route('admin.hero.edit', $hero).'" class="judul-ringkas">Hero lama</a>', 'Hero · 01 Oktober 2026, 10:00',
            ], false)
            ->assertDontSee('Artikel terhapus');
    });

    it('membatasi konten terakhir diperbarui sampai delapan', function () {
        foreach (range(1, 9) as $nomor) {
            Layanan::factory()->create(['judul' => sprintf('Layanan Urut %02d', $nomor), 'updated_at' => now()->subMinutes($nomor)]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSee('Layanan Urut 08')
            ->assertDontSee('Layanan Urut 09');
    });

    it('menampilkan konten belum tampil: artikel draf serta tutorial draf atau tanpa materi, bertautan ke halaman Ubah', function () {
        $artikel = Artikel::factory()->draf()->create(['judul' => 'Artikel draf', 'updated_at' => '2026-10-20 09:00:00']);
        $tutorialDraf = Tutorial::factory()->draf()->create(['judul' => 'Tutorial draf', 'updated_at' => '2026-10-19 09:00:00']);
        $tutorialKosong = Tutorial::factory()->terbit()->create(['judul' => 'Tutorial kosong', 'updated_at' => '2026-10-18 09:00:00']);
        Artikel::factory()->terbit()->create(['judul' => 'Artikel sudah terbit']);
        Tutorial::factory()->terbit()->has(MateriTutorial::factory(), 'materiTutorial')->create(['judul' => 'Tutorial lengkap']);

        $isi = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder([
                '<div class="card-title">Belum tampil</div>',
                'href="'.route('admin.artikel.edit', $artikel).'" class="judul-ringkas">Artikel draf</a>', 'Artikel · 20 Oktober 2026, 09:00', 'chip-yellow">Draf',
                'href="'.route('admin.tutorial.edit', $tutorialDraf).'" class="judul-ringkas">Tutorial draf</a>', 'Tutorial · 19 Oktober 2026, 09:00', 'chip-yellow">Draf',
                'href="'.route('admin.tutorial.edit', $tutorialKosong).'" class="judul-ringkas">Tutorial kosong</a>', 'chip-yellow">Belum ada materi',
            ], false)
            ->getContent();

        // Konten yang sudah tampil boleh muncul di Terakhir diperbarui, tetapi tidak di kartu Belum tampil.
        expect(Str::after($isi, '<div class="card-title">Belum tampil</div>'))
            ->not->toContain('Artikel sudah terbit')
            ->not->toContain('Tutorial lengkap');
    });

    it('menampilkan status website: hero aktif, template aktif per jenis, dan izin mesin pencari', function () {
        Hero::factory()->aktif()->create(['judul' => 'Hero Tampil']);
        Hero::factory()->create(['judul' => 'Hero Cadangan']);
        Template::factory()->aktif()->create(['nama' => 'Warna Aktif']);
        Template::factory()->monochrome()->aktif()->create(['nama' => 'Mono Aktif']);
        Template::factory()->create(['nama' => 'Warna Cadangan']);
        config(['seo.indeks' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder([
                '<div class="card-title">Status website</div>',
                'Hero aktif</th>', '<td>Hero Tampil</td>',
                'Template Full Color</th>', '<td>Warna Aktif</td>',
                'Template Monochrome</th>', '<td>Mono Aktif</td>',
                'Mesin pencari</th>', 'chip-yellow">Tidak diindeks',
                'href="'.route('beranda').'" target="_blank" rel="noopener"', 'Buka website',
            ], false)
            // Hero cadangan tetap boleh muncul di Terakhir diperbarui, tetapi bukan sebagai hero aktif.
            ->assertDontSee('<td>Hero Cadangan</td>', false)
            ->assertDontSee('Warna Cadangan');

        config(['seo.indeks' => true]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('chip-green">Diindeks', false);
    });

    it('mengingatkan modul website yang nonaktif dengan tautan ke Manajemen Modul', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder(['Mesin pencari</th>', 'Modul website</th>', '<span class="chip chip-green">Semua tampil</span>'], false);

        ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Portofolio]);
        ModulWebsite::factory()->nonaktif()->create(['jenis' => JenisModul::Faq]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSee('<a href="'.route('admin.modul-website.index').'" class="chip chip-yellow">2 nonaktif</a>', false);
    });

    it('menampilkan keadaan kosong bila belum ada konten, hero aktif, template aktif, dan konten belum tampil', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada konten')
            ->assertSeeInOrder(['Hero aktif</th>', '<td>—</td>', 'Template Full Color</th>', '<td>—</td>'], false)
            ->assertSee('Semua konten sudah tampil');
    });
});
