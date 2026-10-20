<?php

use App\Http\Controllers\Web\CompanyProfile\TutorialController;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Kategori', 'judul_website' => 'Situs Uji Kategori']);
    $this->akar = rtrim(config('app.url'), '/');
});

/**
 * Tutorial terbit bermateri (tayang) di kategori itu.
 */
function tutorialTayangDi(KategoriTutorial $kategori, array $atribut = []): Tutorial
{
    return Tutorial::factory()->terbit()->for($kategori)->has(MateriTutorial::factory(), 'materiTutorial')->create($atribut);
}

/**
 * Tautan kategori di kotak Kategori kolom samping tutorial, sampai sebelum penutup tag pembukanya.
 */
function tautanKategoriTutorialSamping(string $slug): string
{
    return 'href="'.route('kategori-tutorial.show', $slug).'" class="tautan-samping"';
}

describe('halaman kategori tutorial', function () {
    it('hanya menampilkan tutorial tayang kategori itu, terbaru lebih dulu, dengan judul dan deskripsi halaman', function () {
        $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Web & API', 'slug' => 'web-api']);
        tutorialTayangDi($kategori, ['judul' => 'Tutorial Lama', 'created_at' => now()->subDay()]);
        tutorialTayangDi($kategori, ['judul' => 'Tutorial Baru', 'slug' => 'tutorial-baru']);
        Tutorial::factory()->draf()->for($kategori)->has(MateriTutorial::factory(), 'materiTutorial')->create(['judul' => 'Tutorial Draf']);
        Tutorial::factory()->terbit()->for($kategori)->create(['judul' => 'Tutorial Tanpa Materi']);
        tutorialTayangDi($kategori, ['judul' => 'Tutorial Terhapus'])->delete();
        tutorialTayangDi(KategoriTutorial::factory()->create(), ['judul' => 'Tutorial Kategori Lain']);

        $this->get('/tutorial/kategori/web-api')
            ->assertOk()
            ->assertSee('<title>Tutorial Web &amp; API — PT Uji Kategori</title>', false)
            ->assertSee('<meta name="description" content="Kumpulan tutorial dalam kategori Web &amp; API, terbaru lebih dulu.">', false)
            // Remah roti sampai induknya; nama kategori menjadi h1 tepat di bawahnya.
            ->assertSeeInOrder(['<nav class="remah-roti"', 'href="'.route('tutorial.index').'">Tutorial</a>', '</nav>', '<h1', 'Web &amp; API', '</h1>'], false)
            ->assertSeeInOrder(['Tutorial Baru', 'Tutorial Lama'])
            ->assertSee('<h2 class="kartu-artikel-judul"><a href="'.route('tutorial.show', 'tutorial-baru').'">Tutorial Baru</a></h2>', false)
            ->assertSee('<span class="lencana-artikel">Web &amp; API</span>', false)
            ->assertDontSee('Tutorial Draf')
            ->assertDontSee('Tutorial Tanpa Materi')
            ->assertDontSee('Tutorial Terhapus')
            ->assertDontSee('Tutorial Kategori Lain');
    });

    it('menolak kategori tanpa tutorial tayang, kategori terhapus, slug tak dikenal, dan halaman di luar jangkauan', function () {
        $belumTayang = KategoriTutorial::factory()->create(['slug' => 'belum-tayang']);
        Tutorial::factory()->terbit()->for($belumTayang)->create();
        $terhapus = KategoriTutorial::factory()->create(['slug' => 'kategori-terhapus']);
        tutorialTayangDi($terhapus);
        $terhapus->delete();
        tutorialTayangDi(KategoriTutorial::factory()->create(['slug' => 'kategori-tayang']));

        $this->get('/tutorial/kategori/kategori-tayang')->assertOk();
        $this->get('/tutorial/kategori/belum-tayang')->assertNotFound();
        $this->get('/tutorial/kategori/kategori-terhapus')->assertNotFound();
        $this->get('/tutorial/kategori/tidak-ada')->assertNotFound();
        $this->get('/tutorial/kategori/kategori-tayang?page=2')->assertNotFound();
    });

    it('membagi tutorial sepuluh per halaman dengan alamat kanonis dari slug tersimpan', function () {
        $kategori = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data', 'slug' => 'basis-data']);
        foreach (range(1, TutorialController::PER_HALAMAN + 1) as $nomor) {
            tutorialTayangDi($kategori, ['judul' => sprintf('Tutorial Urut %02d', $nomor), 'created_at' => now()->subDays($nomor)]);
        }

        $this->get('/tutorial/kategori/basis-data')
            ->assertSee('Tutorial Urut 10')
            ->assertDontSee('Tutorial Urut 11')
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/tutorial/kategori/basis-data">', false)
            ->assertSee('rel="next"', false);

        $this->get('/tutorial/kategori/basis-data?page=2')
            ->assertOk()
            ->assertSee('<title>Tutorial Basis Data (Halaman 2) — PT Uji Kategori</title>', false)
            ->assertSee('Tutorial Urut 11')
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/tutorial/kategori/basis-data?page=2">', false);
    });

    it('tidak menutup materi tutorial lain karena slug tutorial kategori dicadangkan', function () {
        tutorialTayangDi(KategoriTutorial::factory()->create(['slug' => 'basis-data']));
        $tutorial = Tutorial::factory()->terbit()->create(['slug' => 'kategori-2']);
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Kategori', 'slug' => 'basis-data']);

        $this->get('/tutorial/kategori/basis-data')->assertOk()->assertSee('<h1', false)->assertDontSee('Materi Kategori');
        $this->get('/tutorial/kategori-2/basis-data')->assertOk()->assertSee('Materi Kategori');
    });

    it('menandai menu Tutorial di navbar sebagai bagiannya', function () {
        tutorialTayangDi(KategoriTutorial::factory()->create(['slug' => 'kategori-menu']));

        expect($this->get('/tutorial/kategori/kategori-menu')->getContent())
            ->toMatch('#href="'.preg_quote(route('tutorial.index'), '#').'" class="tautan-menu"\s+aria-current="true"\s*>Tutorial</a>#');
    });

    it('meng-escape nama kategori', function () {
        tutorialTayangDi(KategoriTutorial::factory()->create(['nama_kategori' => '<script>alert(1)</script>', 'slug' => 'kategori-berbahaya']));

        $this->get('/tutorial/kategori/kategori-berbahaya')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

describe('kotak Kategori di kolom samping tutorial', function () {
    it('menampilkan kategori bertutorial tayang urut abjad beserta jumlahnya di daftar, kategori, dan halaman tutorial', function () {
        $web = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web', 'slug' => 'pemrograman-web']);
        $data = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data', 'slug' => 'basis-data']);
        $belumTayang = KategoriTutorial::factory()->create(['nama_kategori' => 'Kategori Belum Tayang']);
        tutorialTayangDi($web);
        tutorialTayangDi($web);
        // Draf, tutorial tanpa materi, dan tutorial terhapus tidak dihitung.
        Tutorial::factory()->draf()->for($web)->has(MateriTutorial::factory(), 'materiTutorial')->create();
        Tutorial::factory()->terbit()->for($web)->create();
        tutorialTayangDi($web)->delete();
        tutorialTayangDi($data, ['slug' => 'tutorial-data']);
        Tutorial::factory()->terbit()->for($belumTayang)->create();

        foreach ([route('tutorial.index'), '/tutorial/kategori/pemrograman-web', '/tutorial/tutorial-data'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>', false)
                ->assertSeeInOrder([
                    tautanKategoriTutorialSamping('basis-data'), '<span>Basis Data</span>', '<span class="jumlah-kategori">1<span class="sr-only"> tutorial</span></span>',
                    tautanKategoriTutorialSamping('pemrograman-web'), '<span>Pemrograman Web</span>', '<span class="jumlah-kategori">2<span class="sr-only"> tutorial</span></span>',
                ], false)
                ->assertDontSee('Kategori Belum Tayang');
        }
    });

    it('menandai kategori yang sedang dibuka', function () {
        foreach (['basis-data', 'pemrograman-web'] as $slug) {
            tutorialTayangDi(KategoriTutorial::factory()->create(['slug' => $slug]));
        }

        expect($this->get('/tutorial/kategori/pemrograman-web')->getContent())
            ->toMatch('#'.preg_quote(tautanKategoriTutorialSamping('pemrograman-web'), '#').'\s+aria-current="page"\s*>#')
            ->not->toMatch('#'.preg_quote(tautanKategoriTutorialSamping('basis-data'), '#').'\s+aria-current#');
        expect($this->get(route('tutorial.index'))->getContent())->not->toMatch('#class="tautan-samping"\s+aria-current#');
    });

    it('menyusun kotak kolom samping: konsultasi, layanan, lalu kategori; halaman materi tetap tanpa kategori', function () {
        Layanan::factory()->create();
        $tutorial = tutorialTayangDi(KategoriTutorial::factory()->create(['slug' => 'pemrograman-web']), ['slug' => 'tutorial-samping']);

        // Urutan pilihan pemilik 2026-10-20, sama dengan kolom samping artikel.
        $urutan = [
            '<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>',
            '<h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>',
            '<h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>',
        ];

        foreach ([route('tutorial.index'), '/tutorial/kategori/pemrograman-web', '/tutorial/tutorial-samping'] as $alamat) {
            $this->get($alamat)->assertSeeInOrder($urutan, false);
        }

        $this->get(route('materi-tutorial.show', [$tutorial, $tutorial->materiTutorial->first()]))
            ->assertSeeInOrder(['<h2 id="judul-materi-samping" class="judul-samping">Daftar materi</h2>', ...array_slice($urutan, 0, 2)], false)
            ->assertDontSee('judul-kategori-samping', false);
    });

    it('tidak menampilkan kotak Kategori bila belum ada tutorial tayang', function () {
        Tutorial::factory()->terbit()->create();

        $this->get(route('tutorial.index'))
            ->assertOk()
            ->assertDontSee('judul-kategori-samping', false);
    });
});
