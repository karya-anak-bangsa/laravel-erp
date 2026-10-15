<?php

use App\Http\Controllers\Web\CompanyProfile\ArtikelController;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\Layanan;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Kategori', 'judul_website' => 'Situs Uji Kategori']);
    $this->akar = rtrim(config('app.url'), '/');
});

/**
 * Tautan kategori di kotak Kategori kolom samping, sampai sebelum penutup tag pembukanya.
 */
function tautanKategoriSamping(string $slug): string
{
    return 'href="'.route('kategori-artikel.show', $slug).'" class="tautan-samping"';
}

describe('halaman kategori', function () {
    it('hanya menampilkan artikel terbit kategori itu, terbaru lebih dulu, dengan judul dan deskripsi halaman', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Tips & Trik', 'slug' => 'tips-trik']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Lama', 'slug' => 'artikel-lama', 'tanggal' => '2026-09-01']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Baru', 'slug' => 'artikel-baru', 'tanggal' => '2026-10-01']);
        Artikel::factory()->draf()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Draf']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Terhapus'])->delete();
        Artikel::factory()->terbit()->create(['judul' => 'Artikel Kategori Lain']);

        $this->get('/artikel/kategori/tips-trik')
            ->assertOk()
            ->assertSee('<title>Artikel Tips &amp; Trik — PT Uji Kategori</title>', false)
            ->assertSee('<meta name="description" content="Kumpulan artikel dalam kategori Tips &amp; Trik, terbaru lebih dulu.">', false)
            // Remah roti sampai induknya; nama kategori menjadi h1 tepat di bawahnya.
            ->assertSeeInOrder(['<nav class="remah-roti"', 'href="'.route('artikel.index').'">Artikel</a>', '</nav>', '<h1', 'Tips &amp; Trik', '</h1>'], false)
            ->assertSeeInOrder(['Artikel Baru', 'Artikel Lama'])
            ->assertSee('<h2 class="kartu-artikel-judul"><a href="'.route('artikel.show', 'artikel-baru').'" class="tautan-rentang">Artikel Baru</a></h2>', false)
            ->assertSee('<span class="lencana-artikel">Tips &amp; Trik</span>', false)
            ->assertDontSee('Artikel Draf')
            ->assertDontSee('Artikel Terhapus')
            ->assertDontSee('Artikel Kategori Lain');
    });

    it('menolak kategori tanpa artikel terbit, kategori terhapus, slug tak dikenal, dan halaman di luar jangkauan', function () {
        $hanyaDraf = KategoriArtikel::factory()->create(['slug' => 'hanya-draf']);
        Artikel::factory()->draf()->for($hanyaDraf, 'kategoriArtikel')->create();
        $terhapus = KategoriArtikel::factory()->create(['slug' => 'kategori-terhapus']);
        Artikel::factory()->terbit()->for($terhapus, 'kategoriArtikel')->create();
        $terhapus->delete();
        $tayang = KategoriArtikel::factory()->create(['slug' => 'kategori-tayang']);
        Artikel::factory()->terbit()->for($tayang, 'kategoriArtikel')->create();

        $this->get('/artikel/kategori/kategori-tayang')->assertOk();
        $this->get('/artikel/kategori/hanya-draf')->assertNotFound();
        $this->get('/artikel/kategori/kategori-terhapus')->assertNotFound();
        $this->get('/artikel/kategori/tidak-ada')->assertNotFound();
        $this->get('/artikel/kategori/kategori-tayang?page=2')->assertNotFound();
    });

    it('membagi artikel sepuluh per halaman dengan alamat kanonis dari slug tersimpan', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'teknologi']);
        foreach (range(1, ArtikelController::PER_HALAMAN + 1) as $nomor) {
            Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create([
                'judul' => sprintf('Artikel Urut %02d', $nomor),
                'tanggal' => now()->subDays($nomor)->toDateString(),
            ]);
        }

        $this->get('/artikel/kategori/teknologi')
            ->assertSee('Artikel Urut 10')
            ->assertDontSee('Artikel Urut 11')
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel/kategori/teknologi">', false)
            ->assertSee('rel="next"', false);

        $this->get('/artikel/kategori/teknologi?page=2')
            ->assertOk()
            ->assertSee('<title>Artikel Teknologi (Halaman 2) — PT Uji Kategori</title>', false)
            ->assertSee('Artikel Urut 11')
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel/kategori/teknologi?page=2">', false);

        // Pencarian slug tidak peka huruf besar (collation MySQL); kanonis tetap slug tersimpan.
        $this->get('/artikel/kategori/TEKNOLOGI')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel/kategori/teknologi">', false);
    });

    it('menandai menu Artikel di navbar sebagai bagiannya', function () {
        $kategori = KategoriArtikel::factory()->create(['slug' => 'kategori-menu']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create();

        expect($this->get('/artikel/kategori/kategori-menu')->getContent())
            ->toMatch('#href="'.preg_quote(route('artikel.index'), '#').'" class="tautan-menu"\s+aria-current="true"\s*>Artikel</a>#');
    });

    it('meng-escape nama kategori', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => '<script>alert(1)</script>', 'slug' => 'kategori-berbahaya']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create();

        $this->get('/artikel/kategori/kategori-berbahaya')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

describe('kotak Kategori di kolom samping', function () {
    it('menampilkan kategori berartikel terbit urut abjad beserta jumlah artikel terbitnya di daftar, kategori, dan detail', function () {
        $teknologi = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'teknologi']);
        $berita = KategoriArtikel::factory()->create(['nama_kategori' => 'Berita', 'slug' => 'berita']);
        $hanyaDraf = KategoriArtikel::factory()->create(['nama_kategori' => 'Kategori Hanya Draf']);
        Artikel::factory()->terbit()->count(2)->for($teknologi, 'kategoriArtikel')->create();
        // Draf dan artikel terhapus tidak dihitung.
        Artikel::factory()->draf()->for($teknologi, 'kategoriArtikel')->create();
        Artikel::factory()->terbit()->for($teknologi, 'kategoriArtikel')->create()->delete();
        Artikel::factory()->terbit()->for($berita, 'kategoriArtikel')->create(['slug' => 'artikel-berita']);
        Artikel::factory()->draf()->for($hanyaDraf, 'kategoriArtikel')->create();

        foreach ([route('artikel.index'), '/artikel/kategori/teknologi', '/artikel/artikel-berita'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>', false)
                ->assertSeeInOrder([
                    tautanKategoriSamping('berita'), '<span>Berita</span>', '<span class="jumlah-kategori">1<span class="sr-only"> artikel</span></span>',
                    tautanKategoriSamping('teknologi'), '<span>Teknologi</span>', '<span class="jumlah-kategori">2<span class="sr-only"> artikel</span></span>',
                ], false)
                ->assertDontSee('Kategori Hanya Draf');
        }
    });

    it('menandai kategori yang sedang dibuka', function () {
        foreach (['berita', 'teknologi'] as $slug) {
            Artikel::factory()->terbit()->for(KategoriArtikel::factory()->create(['slug' => $slug]), 'kategoriArtikel')->create();
        }

        expect($this->get('/artikel/kategori/teknologi')->getContent())
            ->toMatch('#'.preg_quote(tautanKategoriSamping('teknologi'), '#').'\s+aria-current="page"\s*>#')
            ->not->toMatch('#'.preg_quote(tautanKategoriSamping('berita'), '#').'\s+aria-current#');
        expect($this->get(route('artikel.index'))->getContent())->not->toMatch('#class="tautan-samping"\s+aria-current#');
    });

    it('menyusun kotak kolom samping detail: artikel terbaru, kategori, layanan, lalu konsultasi', function () {
        Layanan::factory()->create();
        Artikel::factory()->terbit()->create(['slug' => 'artikel-dibaca', 'tanggal' => '2026-10-10']);
        Artikel::factory()->terbit()->create(['tanggal' => '2026-10-01']);

        $this->get('/artikel/artikel-dibaca')->assertSeeInOrder([
            '<h2 class="judul-samping">Artikel terbaru</h2>',
            '<h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>',
            '<h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>',
            '<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>',
        ], false);
    });

    it('tidak menampilkan kotak Kategori bila belum ada artikel terbit', function () {
        Artikel::factory()->draf()->create();

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertDontSee('judul-kategori-samping', false);
    });
});
