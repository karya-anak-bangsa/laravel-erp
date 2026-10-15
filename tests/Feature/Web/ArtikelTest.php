<?php

use App\Http\Controllers\Web\CompanyProfile\ArtikelController;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\Layanan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');
    // Judul halaman selain beranda berakhiran nama perusahaan, bukan Judul Website.
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Artikel', 'judul_website' => 'Situs Uji Artikel']);
});

describe('daftar artikel', function () {
    it('hanya menampilkan artikel terbit, terbaru lebih dulu, dengan tautan ke detailnya', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Kategori Uji']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Lama', 'slug' => 'artikel-lama', 'tanggal' => '2026-09-01']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Baru', 'slug' => 'artikel-baru', 'tanggal' => '2026-10-01']);
        Artikel::factory()->draf()->create(['judul' => 'Artikel Draf', 'tanggal' => '2026-10-05']);
        Artikel::factory()->terbit()->create(['judul' => 'Artikel Terhapus', 'tanggal' => '2026-10-06'])->delete();

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertSee('<title>Artikel — PT Uji Artikel</title>', false)
            ->assertSeeInOrder(['<h1', 'Wawasan &amp; kabar terbaru', '</h1>'], false)
            ->assertSeeInOrder(['Artikel Baru', 'Artikel Lama'])
            // Di bawah h1 halaman, judul kartu menjadi h2.
            ->assertSee('<h2 class="kartu-artikel-judul"><a href="'.route('artikel.show', 'artikel-baru').'" class="tautan-rentang">Artikel Baru</a></h2>', false)
            ->assertSee('<span class="lencana-artikel">Kategori Uji</span>', false)
            ->assertDontSee('Artikel Draf')
            ->assertDontSee('Artikel Terhapus');
    });

    it('membagi daftar menjadi sepuluh artikel per halaman', function () {
        foreach (range(1, ArtikelController::PER_HALAMAN + 1) as $nomor) {
            Artikel::factory()->terbit()->create([
                'judul' => sprintf('Artikel Urut %02d', $nomor),
                'tanggal' => now()->subDays($nomor)->toDateString(),
            ]);
        }

        $this->get(route('artikel.index'))
            ->assertSee('Artikel Urut 10')
            ->assertDontSee('Artikel Urut 11')
            ->assertSee('<span class="btn" data-variant="outline" aria-current="page"><span class="sr-only">Halaman </span>1</span>', false)
            ->assertSee('rel="next"', false);

        $this->get(route('artikel.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('<title>Artikel (Halaman 2) — PT Uji Artikel</title>', false)
            ->assertSee('Artikel Urut 11')
            ->assertDontSee('Artikel Urut 10')
            ->assertSee('rel="prev"', false);

        // Halaman di luar jangkauan bukan halaman kosong yang sah.
        $this->get(route('artikel.index', ['page' => 3]))->assertNotFound();
    });

    it('menampilkan pesan bila belum ada artikel terbit', function () {
        Artikel::factory()->draf()->create();

        $this->get(route('artikel.index'))
            ->assertOk()
            ->assertSee('Belum ada artikel yang diterbitkan.')
            ->assertDontSee('class="paginasi"', false);
    });

});

describe('detail artikel', function () {
    it('menampilkan artikel terbit beserta isi, kategori, tanggal, dan meta halaman', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create([
            'judul' => 'Judul Artikel Uji',
            'slug' => 'judul-artikel-uji',
            'tanggal' => '2026-10-09',
            'deskripsi' => '<p>Pembuka artikel.</p><h2>Bagian Pertama</h2><p>Isi <strong>tebal</strong>.</p>',
        ]);

        $this->get('/artikel/judul-artikel-uji')
            ->assertOk()
            ->assertSee('<title>Judul Artikel Uji — PT Uji Artikel</title>', false)
            // Meta deskripsi dari ringkasan isi: sub-judul tidak ikut agar tidak menyambung dengan paragraf.
            ->assertSee('<meta name="description" content="Pembuka artikel. Isi tebal.">', false)
            ->assertSee('id="judul-artikel"', false)
            ->assertSee('class="judul-seksi">Judul Artikel Uji</h1>', false)
            // Kategori sebagai pita di bingkai gambar, tanggal terbit di akhir isi (pilihan pemilik 2026-10-15).
            ->assertSeeInOrder([
                '<div class="bingkai-artikel">',
                '<span class="lencana-artikel">Teknologi</span>',
                '<div class="isi-artikel konten-html"><p>Pembuka artikel.</p><h2>Bagian Pertama</h2><p>Isi <strong>tebal</strong>.</p></div>',
                '<p class="terbit-artikel"><time class="tanggal-artikel" datetime="2026-10-09">Diterbitkan <span class="whitespace-nowrap">9 Oktober 2026</span></time></p>',
                'class="bagikan"',
            ], false)
            ->assertSee('href="'.route('artikel.index').'">Artikel</a>', false);
    });

    it('menyediakan tombol bagikan dengan alamat utama artikel', function () {
        Artikel::factory()->terbit()->create(['judul' => 'Artikel & Bagikan', 'slug' => 'artikel-bagikan']);
        $alamat = rtrim(config('app.url'), '/').'/artikel/artikel-bagikan';

        // Dibuka lewat host lain (mis. www.): yang dibagikan tetap alamat utama.
        $this->get('http://www.contoh.test/artikel/artikel-bagikan')
            ->assertSee('href="https://wa.me/?text='.rawurlencode("Artikel & Bagikan\n".$alamat).'"', false)
            ->assertSee('href="https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($alamat).'"', false)
            ->assertSee('href="https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($alamat).'"', false)
            ->assertSee('<span class="sr-only">Bagikan ke WhatsApp</span>', false)
            ->assertSee('data-salin-tautan="'.$alamat.'"', false)
            // Tombol salin baru ditampilkan JavaScript karena tanpa itu tidak berfungsi.
            ->assertSee('<li hidden>', false);

        $this->get(route('artikel.index'))->assertDontSee('class="bagikan"', false);
    });

    it('menolak artikel draf, artikel terhapus, dan slug yang tidak dikenal', function () {
        Artikel::factory()->draf()->create(['slug' => 'artikel-draf']);
        Artikel::factory()->terbit()->create(['slug' => 'artikel-terhapus'])->delete();

        $this->get('/artikel/artikel-draf')->assertNotFound();
        $this->get('/artikel/artikel-terhapus')->assertNotFound();
        $this->get('/artikel/tidak-ada')->assertNotFound();
    });

    it('menampilkan lima artikel terbit terbaru di kolom samping tanpa artikel yang sedang dibaca', function () {
        Artikel::factory()->terbit()->create(['judul' => 'Artikel Dibaca', 'slug' => 'artikel-dibaca', 'tanggal' => '2026-10-10']);
        foreach (['2026-10-01' => 'Lain Satu', '2026-10-02' => 'Lain Dua', '2026-10-03' => 'Lain Tiga', '2026-10-04' => 'Lain Empat', '2026-10-05' => 'Lain Lima', '2026-10-06' => 'Lain Enam'] as $tanggal => $judul) {
            Artikel::factory()->terbit()->create(['judul' => $judul, 'slug' => Str::slug($judul), 'tanggal' => $tanggal]);
        }
        Artikel::factory()->draf()->create(['judul' => 'Lain Draf', 'tanggal' => '2026-10-09']);

        $this->get('/artikel/artikel-dibaca')
            ->assertSee('<h2 class="judul-samping">Artikel terbaru</h2>', false)
            ->assertSeeInOrder(['Lain Enam', 'Lain Lima', 'Lain Empat', 'Lain Tiga', 'Lain Dua'])
            ->assertSee('<a href="'.route('artikel.show', 'lain-enam').'" class="tautan-terbaru">', false)
            ->assertSee('<time class="tanggal-terbaru" datetime="2026-10-06">6 Oktober 2026</time>', false)
            ->assertDontSee('Lain Satu')
            ->assertDontSee('Lain Draf')
            ->assertDontSee('<span class="judul-terbaru">Artikel Dibaca</span>', false)
            ->assertSee('<a href="'.route('artikel.index').'" class="btn btn-sedang tombol-semua-artikel">Lihat semua artikel</a>', false);
    });

    it('tidak menampilkan artikel terbaru di kolom samping bila tidak ada artikel terbit lain', function () {
        Artikel::factory()->terbit()->create(['slug' => 'satu-satunya']);

        $this->get('/artikel/satu-satunya')
            ->assertOk()
            ->assertSee('<aside class="kolom-samping"', false)
            ->assertDontSee('Artikel terbaru')
            ->assertDontSee('Lihat semua artikel');
    });

    it('meng-escape judul artikel', function () {
        Artikel::factory()->terbit()->create(['judul' => '<script>alert(1)</script>', 'slug' => 'judul-berbahaya']);

        $this->get('/artikel/judul-berbahaya')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

describe('kolom samping', function () {
    it('menautkan layanan ke kartunya di beranda dan menampilkan kartu konsultasi WhatsApp di daftar & detail', function () {
        $identitas = Identitas::query()->sole();
        $kedua = Layanan::factory()->create(['judul' => 'Layanan Kedua', 'urutan_ke' => 2]);
        $pertama = Layanan::factory()->create(['judul' => 'Layanan Pertama', 'urutan_ke' => 1]);
        Artikel::factory()->terbit()->create(['slug' => 'artikel-samping']);

        foreach ([route('artikel.index'), '/artikel/artikel-samping'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>', false)
                ->assertSeeInOrder(['Layanan Pertama', 'Layanan Kedua'])
                ->assertSee('href="'.route('beranda').'#layanan-'.$pertama->id_layanan.'" class="tautan-samping"', false)
                ->assertSee('href="'.route('beranda').'#layanan-'.$kedua->id_layanan.'" class="tautan-samping"', false)
                // Tombol yang sama juga ada di band ajakan; urutan memastikan tombol kartu ada sebelum band.
                ->assertSeeInOrder(['<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>', '<a href="'.$identitas->link_whatsapp.'" target="_blank" rel="noopener" class="btn btn-besar">', '<section id="ajakan"'], false);
        }
    });

    it('mengarahkan kartu konsultasi ke formulir beranda bila link WhatsApp kosong', function () {
        Identitas::query()->sole()->update(['link_whatsapp' => null]);

        $this->get(route('artikel.index'))
            ->assertSeeInOrder(['<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>', '<a href="'.route('beranda').'#kontak" class="btn btn-besar">Kirim Pesan</a>', '<section id="ajakan"'], false);
    });
});

it('menandai menu Artikel di navbar: halaman itu sendiri di daftar, bagiannya di detail', function () {
    Artikel::factory()->terbit()->create(['slug' => 'artikel-aktif']);
    $menuArtikel = fn (string $nilai): string => '#href="'.preg_quote(route('artikel.index'), '#').'" class="tautan-menu"\s+aria-current="'.$nilai.'"\s*>Artikel</a>#';

    expect($this->get(route('artikel.index'))->getContent())->toMatch($menuArtikel('page'));
    expect($this->get('/artikel/artikel-aktif')->getContent())->toMatch($menuArtikel('true'));
    // Di beranda menu Artikel tetap ke halaman artikel, tetapi tidak ditandai.
    expect($this->get('/')->getContent())
        ->toContain('<a href="'.route('artikel.index').'" class="tautan-menu" >Artikel</a>')
        ->not->toContain('aria-current="page" >Artikel');
});
