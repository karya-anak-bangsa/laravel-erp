<?php

use App\Enums\CompanyProfile\JenisProgram;
use App\Http\Controllers\Web\CompanyProfile\TutorialController;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');
    // Judul halaman selain beranda berakhiran nama perusahaan, bukan Judul Website.
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Tutorial', 'judul_website' => 'Situs Uji Tutorial']);
});

/**
 * Tutorial terbit beserta materinya, urut sesuai daftar judul.
 *
 * @param  list<string>  $judulMateri
 */
function tutorialTayang(array $atribut = [], array $judulMateri = ['Materi Satu']): Tutorial
{
    $tutorial = Tutorial::factory()->terbit()->create($atribut);

    foreach ($judulMateri as $urutan => $judul) {
        MateriTutorial::factory()->for($tutorial)->create(['judul' => $judul, 'urutan_ke' => $urutan + 1]);
    }

    return $tutorial;
}

describe('daftar tutorial', function () {
    it('hanya menampilkan tutorial terbit yang punya materi, terbaru lebih dulu, beserta jumlah materinya', function () {
        tutorialTayang(['judul' => 'Tutorial Lama', 'created_at' => now()->subDay()]);
        $baru = tutorialTayang([
            'judul' => 'Tutorial Baru',
            'slug' => 'tutorial-baru',
            'jenis_program' => JenisProgram::Bootcamp,
            'deskripsi' => '<p>Belajar <strong>Laravel</strong> dari awal.</p>',
        ], ['Materi A', 'Materi B', 'Materi C']);
        // Materi terhapus tidak ikut dihitung.
        $baru->materiTutorial()->first()->delete();
        Tutorial::factory()->draf()->has(MateriTutorial::factory())->create(['judul' => 'Tutorial Draf']);
        Tutorial::factory()->terbit()->create(['judul' => 'Tutorial Tanpa Materi']);
        tutorialTayang(['judul' => 'Tutorial Terhapus'])->delete();

        $this->get(route('tutorial.index'))
            ->assertOk()
            ->assertSee('<title>Tutorial — PT Uji Tutorial</title>', false)
            ->assertSeeInOrder(['<h1', 'Belajar langkah demi langkah', '</h1>'], false)
            ->assertSeeInOrder(['Tutorial Baru', 'Tutorial Lama'])
            // Kartu portofolio dipakai bersama: pita jenis program, judul h2 bertautan, ringkasan, lalu jumlah materi.
            ->assertSeeInOrder([
                '<article class="kartu-portofolio corak-rose">',
                '<span class="pita-kategori">Bootcamp</span>',
                '<h2 class="judul-portofolio"><a href="'.route('tutorial.show', 'tutorial-baru').'">Tutorial Baru</a></h2>',
                '<p class="deskripsi-portofolio">Belajar Laravel dari awal.</p>',
                '<p class="jumlah-materi">2 materi</p>',
            ], false)
            ->assertDontSee('Tutorial Draf')
            ->assertDontSee('Tutorial Tanpa Materi')
            ->assertDontSee('Tutorial Terhapus');
    });

    it('membagi daftar menjadi sepuluh tutorial per halaman', function () {
        foreach (range(1, TutorialController::PER_HALAMAN + 1) as $nomor) {
            tutorialTayang(['judul' => sprintf('Tutorial Urut %02d', $nomor), 'created_at' => now()->subDays($nomor)]);
        }

        $this->get(route('tutorial.index'))
            ->assertSee('Tutorial Urut 10')
            ->assertDontSee('Tutorial Urut 11')
            ->assertSee('rel="next"', false);

        $this->get(route('tutorial.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('<title>Tutorial (Halaman 2) — PT Uji Tutorial</title>', false)
            ->assertSee('Tutorial Urut 11')
            ->assertDontSee('Tutorial Urut 10');

        // Halaman di luar jangkauan bukan halaman kosong yang sah.
        $this->get(route('tutorial.index', ['page' => 3]))->assertNotFound();
    });

    it('menampilkan pesan bila belum ada tutorial yang tayang', function () {
        Tutorial::factory()->terbit()->create();

        $this->get(route('tutorial.index'))
            ->assertOk()
            ->assertSee('Belum ada tutorial. Silakan kunjungi lagi nanti.')
            ->assertDontSee('class="paginasi"', false);
    });
});

describe('halaman tutorial', function () {
    it('menampilkan sampul, pengantar, materi bernomor sesuai urutan, dan tombol ke materi pertama', function () {
        $tutorial = Tutorial::factory()->terbit()->create([
            'judul' => 'Tutorial Laravel Uji',
            'slug' => 'tutorial-laravel-uji',
            'jenis_program' => JenisProgram::PelatihanIt,
            'deskripsi' => '<p>Seri uji untuk pemula.</p>',
        ]);
        // Urutan tampil: urutan_ke, lalu urutan dibuat untuk angka yang sama; nomornya dari posisi, bukan urutan_ke.
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Ketiga', 'slug' => 'materi-ketiga', 'urutan_ke' => 7]);
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Pertama', 'slug' => 'materi-pertama', 'urutan_ke' => 2]);
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Kedua', 'slug' => 'materi-kedua', 'urutan_ke' => 2]);
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Terhapus', 'urutan_ke' => 1])->delete();
        MateriTutorial::factory()->create(['judul' => 'Materi Tutorial Lain']);

        $this->get('/tutorial/tutorial-laravel-uji')
            ->assertOk()
            ->assertSee('<title>Tutorial Laravel Uji — PT Uji Tutorial</title>', false)
            ->assertSee('<meta name="description" content="Seri uji untuk pemula.">', false)
            ->assertSeeInOrder([
                '<nav class="remah-roti"', 'href="'.route('tutorial.index').'">Tutorial</a>', '</nav>',
                'class="judul-seksi">Tutorial Laravel Uji</h1>',
                '<div class="bingkai-artikel">',
                '<span class="pita-kategori">Pelatihan IT</span>',
                '<img src="'.$tutorial->gambar_url.'"',
                '<div class="isi-artikel konten-html"><p>Seri uji untuk pemula.</p></div>',
                '<h2 id="judul-daftar-materi" class="judul-daftar-materi">Daftar materi</h2>',
                'href="'.route('materi-tutorial.show', ['tutorial-laravel-uji', 'materi-pertama']).'" class="tautan-materi"',
                '<span class="nomor-materi">01</span>', 'Materi Pertama',
                '<span class="nomor-materi">02</span>', 'Materi Kedua',
                '<span class="nomor-materi">03</span>', 'Materi Ketiga',
                '<a href="'.route('materi-tutorial.show', ['tutorial-laravel-uji', 'materi-pertama']).'" class="btn btn-besar tombol-mulai">Mulai belajar</a>',
            ], false)
            ->assertDontSee('Materi Terhapus')
            ->assertDontSee('Materi Tutorial Lain')
            // Halaman tutorial tidak menandai materi mana pun.
            ->assertDontSee('aria-current="page"', false);
    });

    it('menolak tutorial draf, tanpa materi, terhapus, dan slug yang tidak dikenal', function () {
        Tutorial::factory()->draf()->has(MateriTutorial::factory())->create(['slug' => 'tutorial-draf']);
        Tutorial::factory()->terbit()->create(['slug' => 'tutorial-kosong']);
        $materiTerhapus = tutorialTayang(['slug' => 'materi-terhapus-semua']);
        $materiTerhapus->materiTutorial()->first()->delete();
        tutorialTayang(['slug' => 'tutorial-terhapus'])->delete();

        foreach (['tutorial-draf', 'tutorial-kosong', 'materi-terhapus-semua', 'tutorial-terhapus', 'tidak-ada'] as $slug) {
            $this->get("/tutorial/{$slug}")->assertNotFound();
        }
    });

    it('meng-escape judul tutorial', function () {
        tutorialTayang(['judul' => '<script>alert(1)</script>', 'slug' => 'judul-berbahaya']);

        $this->get('/tutorial/judul-berbahaya')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

describe('halaman materi', function () {
    beforeEach(function () {
        $this->tutorial = Tutorial::factory()->terbit()->create(['judul' => 'Tutorial Seri', 'slug' => 'tutorial-seri']);
        foreach (['Materi Awal', 'Materi Tengah', 'Materi Akhir'] as $urutan => $judul) {
            MateriTutorial::factory()->for($this->tutorial)->create(['judul' => $judul, 'slug' => Str::slug($judul), 'urutan_ke' => $urutan + 1]);
        }
        $this->alamat = fn (string $slug): string => route('materi-tutorial.show', ['tutorial-seri', $slug]);
    });

    it('menampilkan nomor materi, isi berkode, dan tautan materi sebelumnya & berikutnya', function () {
        $this->tutorial->materiTutorial()->where('slug', 'materi-tengah')->update([
            'deskripsi' => '<p>Jalankan <code>php artisan serve</code>.</p><h2>Langkah</h2><pre><code>Route::get(\'/\', fn () =&gt; view(\'welcome\'));</code></pre>',
        ]);

        $this->get('/tutorial/tutorial-seri/materi-tengah')
            ->assertOk()
            ->assertSee('<title>Materi Tengah — Tutorial Seri — PT Uji Tutorial</title>', false)
            // Blok kode dan sub-judul bukan kalimat, jadi tidak ikut ringkasan.
            ->assertSee('<meta name="description" content="Jalankan php artisan serve.">', false)
            ->assertSeeInOrder([
                // Remah roti sampai halaman tutorialnya; nomor materi dari posisi di antara materi tutorial ini.
                '<nav class="remah-roti"', 'href="'.route('tutorial.index').'">Tutorial</a>', 'href="'.route('tutorial.show', 'tutorial-seri').'">Tutorial Seri</a>', '</nav>',
                '<p class="lencana-artikel urutan-materi">Materi 2 dari 3</p>',
                'class="judul-seksi">Materi Tengah</h1>',
                '<div class="isi-artikel isi-materi konten-html"><p>Jalankan <code>php artisan serve</code>.</p><h2>Langkah</h2><pre><code>Route::get(\'/\', fn () =&gt; view(\'welcome\'));</code></pre></div>',
                '<nav class="navigasi-materi" aria-label="Materi sebelumnya dan berikutnya">',
                '<a href="'.($this->alamat)('materi-awal').'" rel="prev" class="tautan-navigasi-materi">', 'Materi Awal',
                '<a href="'.($this->alamat)('materi-akhir').'" rel="next" class="tautan-navigasi-materi tautan-materi-berikutnya">', 'Materi Akhir',
                // Kolom samping: Daftar materi lebih dulu, materi yang dibuka ditandai, lalu Konsultasi & Layanan.
                '<aside class="kolom-samping"',
                '<h2 id="judul-materi-samping" class="judul-samping">Daftar materi</h2>',
                '<ol class="daftar-materi daftar-materi-samping">',
            ], false)
            ->assertDontSee('bingkai-artikel', false);

        expect($this->get('/tutorial/tutorial-seri/materi-tengah')->getContent())
            ->toMatch('#href="'.preg_quote(($this->alamat)('materi-tengah'), '#').'" class="tautan-materi"\s+aria-current="page"\s*>#')
            ->not->toMatch('#href="'.preg_quote(($this->alamat)('materi-awal'), '#').'" class="tautan-materi"\s+aria-current#');
    });

    it('hanya menautkan materi berikutnya di materi pertama dan materi sebelumnya di materi terakhir', function () {
        $this->get('/tutorial/tutorial-seri/materi-awal')
            ->assertSee('<p class="lencana-artikel urutan-materi">Materi 1 dari 3</p>', false)
            ->assertSee('rel="next"', false)
            ->assertDontSee('rel="prev"', false);

        $this->get('/tutorial/tutorial-seri/materi-akhir')
            ->assertSee('<p class="lencana-artikel urutan-materi">Materi 3 dari 3</p>', false)
            ->assertSee('rel="prev"', false)
            ->assertDontSee('rel="next"', false);
    });

    it('tidak menampilkan navigasi materi bila tutorial hanya punya satu materi', function () {
        tutorialTayang(['slug' => 'seri-tunggal'], ['Satu-satunya']);

        $this->get('/tutorial/seri-tunggal/satu-satunya')
            ->assertOk()
            ->assertSee('Materi 1 dari 1')
            ->assertDontSee('class="navigasi-materi"', false);
    });

    it('menolak materi tutorial lain, materi terhapus, tutorial draf, dan slug yang tidak dikenal', function () {
        tutorialTayang(['slug' => 'tutorial-lain'], ['Materi Seri Lain']);
        $this->tutorial->materiTutorial()->where('slug', 'materi-akhir')->first()->delete();
        $draf = Tutorial::factory()->draf()->create(['slug' => 'tutorial-draf']);
        MateriTutorial::factory()->for($draf)->create(['slug' => 'materi-draf']);

        $this->get('/tutorial/tutorial-seri/materi-seri-lain')->assertNotFound();
        $this->get('/tutorial/tutorial-seri/materi-akhir')->assertNotFound();
        $this->get('/tutorial/tutorial-draf/materi-draf')->assertNotFound();
        $this->get('/tutorial/tutorial-seri/tidak-ada')->assertNotFound();
        $this->get('/tutorial/tidak-ada/materi-awal')->assertNotFound();
    });

    it('meng-escape judul materi', function () {
        tutorialTayang(['slug' => 'seri-berbahaya'], ['<script>alert(1)</script>']);
        $slug = MateriTutorial::query()->latest('id_materi_tutorial')->value('slug');

        $this->get("/tutorial/seri-berbahaya/{$slug}")
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

it('menampilkan kartu konsultasi WhatsApp dan layanan di kolom samping semua halaman tutorial', function () {
    $identitas = Identitas::query()->sole();
    $layanan = Layanan::factory()->create(['judul' => 'Layanan Uji']);
    tutorialTayang(['slug' => 'tutorial-samping'], ['Materi Samping']);

    foreach ([route('tutorial.index'), '/tutorial/tutorial-samping', '/tutorial/tutorial-samping/materi-samping'] as $alamat) {
        $this->get($alamat)
            ->assertSee('<h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>', false)
            ->assertSee('href="'.route('beranda').'#layanan-'.$layanan->id_layanan.'" class="tautan-samping"', false)
            ->assertSeeInOrder(['<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>', '<a href="'.$identitas->link_whatsapp.'" target="_blank" rel="noopener" class="btn btn-besar">', '<section id="ajakan"'], false);
    }
});

it('menandai menu Tutorial di navbar: halaman itu sendiri di daftar, bagiannya di halaman tutorial & materi', function () {
    tutorialTayang(['slug' => 'tutorial-aktif'], ['Materi Aktif']);
    $menuTutorial = fn (string $nilai): string => '#href="'.preg_quote(route('tutorial.index'), '#').'" class="tautan-menu"\s+aria-current="'.$nilai.'"\s*>Tutorial</a>#';

    expect($this->get(route('tutorial.index'))->getContent())->toMatch($menuTutorial('page'));
    expect($this->get('/tutorial/tutorial-aktif')->getContent())->toMatch($menuTutorial('true'));
    expect($this->get('/tutorial/tutorial-aktif/materi-aktif')->getContent())->toMatch($menuTutorial('true'));
    // Dari halaman lain, menu navbar dan footer menuju daftar tutorial tanpa penanda, tepat setelah Portofolio.
    expect($this->get(route('artikel.index'))->getContent())
        ->toMatch('#>Portofolio</a></li>\s*<li><a href="'.preg_quote(route('tutorial.index'), '#').'" class="tautan-menu"\s*>Tutorial</a>#')
        ->toContain('<a href="'.route('tutorial.index').'" class="tautan-kaki">Tutorial</a>');
});
