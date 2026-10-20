<?php

use App\Http\Controllers\Web\CompanyProfile\ArtikelController;
use App\Http\Controllers\Web\CompanyProfile\PortofolioController;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;
use App\Support\Seo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->identitas = Identitas::factory()->create([
        'nama_perusahaan' => 'PT Uji SEO',
        'judul_website' => 'Situs Uji SEO',
        'meta_deskripsi' => 'Ringkasan situs uji.',
        'telepon' => '0812-3456-7890',
        'link_instagram' => 'https://www.instagram.com/akunuji',
        'link_youtube' => 'https://www.youtube.com/@kanaluji',
    ]);
    // Alamat kanonis & sitemap dibentuk dari APP_URL, bukan dari host request.
    $this->akar = rtrim(config('app.url'), '/');
});

/**
 * Semua JSON-LD di halaman, sudah diurai.
 *
 * @return list<array<string, mixed>>
 */
function jsonLdHalaman(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $cocok);

    return array_map(fn (string $json): array => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $cocok[1]);
}

/**
 * Gambar sungguhan di disk public palsu, agar ukurannya bisa dibaca.
 */
function simpanGambarUji(string $path, int $lebar, int $tinggi): string
{
    Storage::disk('public')->putFileAs(dirname($path), UploadedFile::fake()->image(basename($path), $lebar, $tinggi), basename($path));

    return $path;
}

describe('judul, deskripsi, dan alamat kanonis', function () {
    it('memakai Judul Website di beranda dan akhiran nama perusahaan di halaman lain', function () {
        $this->get('/')
            ->assertSee('<title>Situs Uji SEO</title>', false)
            ->assertSee('<meta name="description" content="Ringkasan situs uji.">', false)
            ->assertSee('<link rel="canonical" href="'.$this->akar.'">', false)
            ->assertSee('<meta property="og:title" content="Situs Uji SEO">', false)
            ->assertSee('<meta property="og:description" content="Ringkasan situs uji.">', false)
            ->assertSee('<meta property="og:url" content="'.$this->akar.'">', false)
            ->assertSee('<meta property="og:site_name" content="PT Uji SEO">', false)
            ->assertSee('<meta property="og:type" content="website">', false);

        $this->get('/artikel')
            ->assertSee('<title>Artikel — PT Uji SEO</title>', false)
            ->assertSee('<meta property="og:title" content="Artikel — PT Uji SEO">', false);
    });

    it('membentuk alamat kanonis dari APP_URL tanpa parameter tambahan', function () {
        // Dibuka lewat host lain (mis. www.) dengan parameter pelacak: kanonis tetap alamat utama.
        $this->get('http://www.contoh.test/artikel?utm_source=whatsapp')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel">', false)
            ->assertSee('<meta property="og:url" content="'.$this->akar.'/artikel">', false);
    });

    it('memberi halaman paginasi alamat kanonisnya sendiri', function () {
        Artikel::factory()->terbit()->count(ArtikelController::PER_HALAMAN + 1)->create();
        Portofolio::factory()->count(PortofolioController::PER_HALAMAN + 1)->create();

        foreach (['artikel', 'portofolio'] as $daftar) {
            $this->get("/{$daftar}?page=2")->assertSee('<link rel="canonical" href="'.$this->akar."/{$daftar}?page=2\">", false);
            $this->get("/{$daftar}?page=1")->assertSee('<link rel="canonical" href="'.$this->akar."/{$daftar}\">", false);
        }
    });

    it('memakai slug tersimpan sebagai alamat kanonis artikel', function () {
        Artikel::factory()->terbit()->create(['slug' => 'artikel-kanonis']);

        // Pencarian slug tidak peka huruf besar (collation MySQL), jadi alamat ini tetap menemukan artikelnya.
        $this->get('/artikel/ARTIKEL-KANONIS')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel/artikel-kanonis">', false);
    });

    it('tidak merender meta deskripsi yang kosong', function () {
        $this->identitas->update(['meta_deskripsi' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('<meta name="description"', false)
            ->assertDontSee('og:description', false);
    });
});

describe('robots', function () {
    it('mengizinkan pengindeksan halaman publik secara bawaan', function () {
        Artikel::factory()->terbit()->for(KategoriArtikel::factory()->create(['slug' => 'kategori-terindeks']), 'kategoriArtikel')
            ->create(['slug' => 'artikel-terindeks']);
        Portofolio::factory()->create(['slug' => 'portofolio-terindeks']);

        foreach (['/', '/artikel', '/artikel/kategori/kategori-terindeks', '/artikel/artikel-terindeks', '/portofolio', '/portofolio/portofolio-terindeks'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<meta name="robots" content="max-image-preview:large">', false)
                ->assertDontSee('noindex', false);
        }
    });

    it('menandai semua halaman publik noindex bila pengindeksan ditutup', function () {
        config(['seo.indeks' => false]);
        Artikel::factory()->terbit()->for(KategoriArtikel::factory()->create(['slug' => 'kategori-tertutup']), 'kategoriArtikel')
            ->create(['slug' => 'artikel-tertutup']);
        Portofolio::factory()->create(['slug' => 'portofolio-tertutup']);

        foreach (['/', '/artikel', '/artikel/kategori/kategori-tertutup', '/artikel/artikel-tertutup', '/portofolio', '/portofolio/portofolio-tertutup'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<meta name="robots" content="noindex">', false)
                ->assertDontSee('max-image-preview', false);
        }
    });

    it('menandai halaman login dan panel admin noindex', function () {
        $this->get('/login')->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->actingAs(Pengguna::factory()->create())
            ->get('/admin')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    });
});

describe('pratinjau tautan', function () {
    it('memakai gambar hero aktif beserta ukurannya di beranda dan halaman daftar', function () {
        Hero::factory()->aktif()->create([
            'judul' => 'Hero & Uji',
            'gambar' => simpanGambarUji('company-profile/hero/hero-uji.webp', 1200, 630),
        ]);

        foreach (['/', '/artikel', '/portofolio'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<meta property="og:image" content="'.$this->akar.'/storage/company-profile/hero/hero-uji.webp">', false)
                ->assertSee('<meta property="og:image:width" content="1200">', false)
                ->assertSee('<meta property="og:image:height" content="630">', false)
                ->assertSee('<meta property="og:image:alt" content="Hero &amp; Uji">', false)
                ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        }
    });

    it('tidak menyebut gambar tanpa hero aktif', function () {
        Hero::factory()->create();

        $this->get('/')
            ->assertDontSee('og:image', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false);
    });

    it('memakai judul, gambar, dan tanggal artikel di halaman detail', function () {
        Hero::factory()->aktif()->create(['gambar' => 'company-profile/hero/hero-lain.webp']);
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi']);
        $artikel = Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create([
            'judul' => 'Tips & "Trik" Website',
            'slug' => 'tips-trik-website',
            'tanggal' => '2026-10-09',
            'gambar' => simpanGambarUji('company-profile/artikel/artikel-uji.webp', 800, 600),
        ]);

        $this->get('/artikel/tips-trik-website')
            ->assertSee('<meta property="og:type" content="article">', false)
            // Di-escape sekali saja walau melewati section dan composer.
            ->assertSee('<title>Tips &amp; &quot;Trik&quot; Website — PT Uji SEO</title>', false)
            ->assertSee('<meta property="og:title" content="Tips &amp; &quot;Trik&quot; Website — PT Uji SEO">', false)
            ->assertSee('<meta property="og:image" content="'.$this->akar.'/storage/company-profile/artikel/artikel-uji.webp">', false)
            ->assertSee('<meta property="og:image:width" content="800">', false)
            ->assertSee('<meta property="og:image:alt" content="Tips &amp; &quot;Trik&quot; Website">', false)
            ->assertDontSee('hero-lain.webp', false)
            ->assertSee('<meta property="article:published_time" content="2026-10-09T00:00:00+07:00">', false)
            ->assertSee('<meta property="article:modified_time" content="'.$artikel->updated_at->toIso8601String().'">', false)
            ->assertSee('<meta property="article:section" content="Teknologi">', false);
    });

    it('memakai judul dan gambar portofolio di halaman detail', function () {
        Hero::factory()->aktif()->create(['gambar' => 'company-profile/hero/hero-lain.webp']);
        Portofolio::factory()->create([
            'judul' => 'Toko "Online" & Kerajinan',
            'slug' => 'toko-online-kerajinan',
            'gambar' => simpanGambarUji('company-profile/portofolio/portofolio-uji.webp', 800, 600),
        ]);

        // Dibuka dengan huruf besar: kanonis tetap dari slug tersimpan.
        $this->get('/portofolio/TOKO-ONLINE-KERAJINAN')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$this->akar.'/portofolio/toko-online-kerajinan">', false)
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<title>Toko &quot;Online&quot; &amp; Kerajinan — PT Uji SEO</title>', false)
            ->assertSee('<meta property="og:image" content="'.$this->akar.'/storage/company-profile/portofolio/portofolio-uji.webp">', false)
            ->assertSee('<meta property="og:image:width" content="800">', false)
            ->assertSee('<meta property="og:image:alt" content="Toko &quot;Online&quot; &amp; Kerajinan">', false)
            ->assertDontSee('hero-lain.webp', false);
    });

    it('memakai sampul tutorial di halaman tutorial dan materinya', function () {
        Hero::factory()->aktif()->create(['gambar' => 'company-profile/hero/hero-lain.webp']);
        $tutorial = Tutorial::factory()->terbit()->create([
            'slug' => 'tutorial-uji',
            'gambar' => simpanGambarUji('company-profile/tutorial/sampul-uji.webp', 800, 600),
        ]);
        MateriTutorial::factory()->for($tutorial)->create(['slug' => 'materi-uji']);

        // Dibuka dengan huruf besar: kanonis tetap dari slug tersimpan. Materi tanpa gambar memakai sampul tutorialnya.
        foreach (['/tutorial/TUTORIAL-UJI' => '/tutorial/tutorial-uji', '/tutorial/TUTORIAL-UJI/MATERI-UJI' => '/tutorial/tutorial-uji/materi-uji'] as $alamat => $kanonis) {
            $this->get($alamat)
                ->assertOk()
                ->assertSee('<link rel="canonical" href="'.$this->akar.$kanonis.'">', false)
                ->assertSee('<meta property="og:image" content="'.$this->akar.'/storage/company-profile/tutorial/sampul-uji.webp">', false)
                ->assertSee('<meta property="og:image:width" content="800">', false)
                ->assertDontSee('hero-lain.webp', false);
        }
    });
});

describe('data terstruktur', function () {
    it('menjelaskan organisasi dan situs di beranda', function () {
        [$data] = jsonLdHalaman($this->get('/')->getContent());
        [$organisasi, $situs] = $data['@graph'];

        expect($data['@context'])->toBe('https://schema.org')
            ->and($organisasi)->toMatchArray([
                '@type' => 'Organization',
                '@id' => $this->akar.'/#organisasi',
                'name' => 'PT Uji SEO',
                'url' => $this->akar,
                'logo' => $this->akar.'/storage/'.$this->identitas->logo_website,
                'description' => 'Ringkasan situs uji.',
                'telephone' => '+6281234567890',
                'sameAs' => ['https://www.instagram.com/akunuji', 'https://www.youtube.com/@kanaluji'],
            ])
            ->and($organisasi['address'])->toMatchArray(['@type' => 'PostalAddress', 'streetAddress' => $this->identitas->alamat])
            ->and($situs)->toMatchArray(['@type' => 'WebSite', 'url' => $this->akar, 'name' => 'PT Uji SEO']);
    });

    it('menjelaskan artikel beserta remah rotinya', function () {
        $artikel = Artikel::factory()->terbit()->create(['judul' => 'Artikel Terstruktur', 'slug' => 'artikel-terstruktur', 'tanggal' => '2026-10-09']);

        [$data] = jsonLdHalaman($this->get('/artikel/artikel-terstruktur')->getContent());
        [$posting, $remahRoti] = $data['@graph'];

        expect($posting)->toMatchArray([
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => $this->akar.'/artikel/artikel-terstruktur',
            'headline' => 'Artikel Terstruktur',
            'image' => [$this->akar.'/storage/'.$artikel->gambar],
            'datePublished' => '2026-10-09T00:00:00+07:00',
            'author' => ['@type' => 'Organization', 'name' => 'PT Uji SEO', 'url' => $this->akar],
        ])
            ->and($remahRoti['itemListElement'])->toBe([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $this->akar],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Artikel', 'item' => $this->akar.'/artikel'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Artikel Terstruktur'],
            ]);

        [$daftar] = jsonLdHalaman($this->get('/artikel')->getContent());
        expect($daftar['@graph'][0]['itemListElement'])->toHaveCount(2);
    });

    it('menjelaskan remah roti halaman kategori', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Tips & Tutorial', 'slug' => 'tips-tutorial']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create();

        [$data] = jsonLdHalaman($this->get('/artikel/kategori/tips-tutorial')->getContent());

        expect($data['@graph'])->toHaveCount(1)
            ->and($data['@graph'][0]['itemListElement'])->toBe([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $this->akar],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Artikel', 'item' => $this->akar.'/artikel'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Tips & Tutorial'],
            ]);
    });

    it('menjelaskan remah roti halaman daftar dan detail portofolio', function () {
        Portofolio::factory()->create(['judul' => 'Proyek & Uji', 'slug' => 'proyek-uji']);

        [$detail] = jsonLdHalaman($this->get('/portofolio/proyek-uji')->getContent());
        [$daftar] = jsonLdHalaman($this->get('/portofolio')->getContent());

        expect($detail['@graph'])->toHaveCount(1)
            ->and($detail['@graph'][0]['itemListElement'])->toBe([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $this->akar],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Portofolio', 'item' => $this->akar.'/portofolio'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Proyek & Uji'],
            ])
            ->and($daftar['@graph'][0]['itemListElement'])->toBe([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $this->akar],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Portofolio'],
            ]);
    });

    it('menjelaskan remah roti halaman daftar tutorial, tutorial, dan materinya', function () {
        $tutorial = Tutorial::factory()->terbit()->create(['judul' => 'Tutorial & Uji', 'slug' => 'tutorial-uji']);
        MateriTutorial::factory()->for($tutorial)->create(['judul' => 'Materi Pertama', 'slug' => 'materi-pertama']);

        $tutorial->kategoriTutorial->update(['nama_kategori' => 'Web & API', 'slug' => 'web-api']);

        [$daftar] = jsonLdHalaman($this->get('/tutorial')->getContent());
        [$kategori] = jsonLdHalaman($this->get('/tutorial/kategori/web-api')->getContent());
        [$halamanTutorial] = jsonLdHalaman($this->get('/tutorial/tutorial-uji')->getContent());
        [$materi] = jsonLdHalaman($this->get('/tutorial/tutorial-uji/materi-pertama')->getContent());

        $beranda = ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $this->akar];
        $induk = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tutorial', 'item' => $this->akar.'/tutorial'];

        expect($daftar['@graph'][0]['itemListElement'])->toBe([$beranda, ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tutorial']])
            ->and($kategori['@graph'][0]['itemListElement'])->toBe([$beranda, $induk, ['@type' => 'ListItem', 'position' => 3, 'name' => 'Web & API']])
            ->and($halamanTutorial['@graph'])->toHaveCount(1)
            ->and($halamanTutorial['@graph'][0]['itemListElement'])->toBe([$beranda, $induk, ['@type' => 'ListItem', 'position' => 3, 'name' => 'Tutorial & Uji']])
            ->and($materi['@graph'][0]['itemListElement'])->toBe([
                $beranda,
                $induk,
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Tutorial & Uji', 'item' => $this->akar.'/tutorial/tutorial-uji'],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'Materi Pertama'],
            ]);
    });

    it('tidak bisa ditutup paksa oleh judul artikel', function () {
        Artikel::factory()->terbit()->create(['judul' => '</script><script>alert(1)</script>', 'slug' => 'judul-jahat']);

        $html = $this->get('/artikel/judul-jahat')->getContent();

        expect($html)->not->toContain('<script>alert(1)</script>')
            ->and(jsonLdHalaman($html)[0]['@graph'][0]['headline'])->toBe('</script><script>alert(1)</script>');
    });
});

describe('sitemap.xml', function () {
    it('memuat beranda, artikel terbit beserta daftar & kategorinya, semua portofolio, dan tutorial yang tayang', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'teknologi', 'updated_at' => '2026-09-01 07:00:00']);
        $berita = KategoriArtikel::factory()->create(['nama_kategori' => 'Berita', 'slug' => 'berita', 'updated_at' => '2026-10-08 09:00:00']);
        $hanyaDraf = KategoriArtikel::factory()->create(['slug' => 'hanya-draf']);
        $terhapus = KategoriArtikel::factory()->create(['slug' => 'kategori-terhapus']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['slug' => 'artikel-lama', 'tanggal' => '2026-09-01', 'updated_at' => '2026-10-01 10:00:00']);
        Artikel::factory()->terbit()->for($berita, 'kategoriArtikel')->create(['slug' => 'artikel-baru', 'tanggal' => '2026-10-01', 'updated_at' => '2026-10-05 08:30:00']);
        Artikel::factory()->draf()->for($hanyaDraf, 'kategoriArtikel')->create(['slug' => 'artikel-draf']);
        Artikel::factory()->terbit()->for($terhapus, 'kategoriArtikel')->create(['slug' => 'artikel-terhapus'])->delete();
        Portofolio::factory()->create(['slug' => 'portofolio-lama', 'created_at' => '2026-09-10 08:00:00', 'updated_at' => '2026-09-20 08:00:00']);
        Portofolio::factory()->create(['slug' => 'portofolio-baru', 'created_at' => '2026-10-02 08:00:00', 'updated_at' => '2026-10-03 08:00:00']);
        Portofolio::factory()->create(['slug' => 'portofolio-terhapus'])->delete();
        $web = KategoriTutorial::factory()->create(['nama_kategori' => 'Pemrograman Web', 'slug' => 'pemrograman-web', 'updated_at' => '2026-09-01 07:00:00']);
        $basisData = KategoriTutorial::factory()->create(['nama_kategori' => 'Basis Data', 'slug' => 'basis-data', 'updated_at' => '2026-10-07 07:00:00']);
        $tutorial = Tutorial::factory()->terbit()->for($web)->create(['slug' => 'tutorial-uji', 'created_at' => '2026-09-14 08:00:00', 'updated_at' => '2026-09-15 08:00:00']);
        MateriTutorial::factory()->for($tutorial)->create(['slug' => 'materi-kedua', 'urutan_ke' => 2, 'updated_at' => '2026-09-16 08:00:00']);
        MateriTutorial::factory()->for($tutorial)->create(['slug' => 'materi-pertama', 'urutan_ke' => 1, 'updated_at' => '2026-10-04 09:00:00']);
        MateriTutorial::factory()->for($tutorial)->create(['slug' => 'materi-terhapus', 'updated_at' => '2026-10-06 09:00:00'])->delete();
        $tutorialData = Tutorial::factory()->terbit()->for($basisData)->create(['slug' => 'tutorial-data', 'created_at' => '2026-09-01 08:00:00', 'updated_at' => '2026-09-02 08:00:00']);
        MateriTutorial::factory()->for($tutorialData)->create(['slug' => 'materi-data', 'updated_at' => '2026-09-03 08:00:00']);
        Tutorial::factory()->draf()->has(MateriTutorial::factory(), 'materiTutorial')->create(['slug' => 'tutorial-draf']);
        Tutorial::factory()->terbit()->create(['slug' => 'tutorial-kosong']);

        $respons = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        // Deklarasi XML wajib di karakter pertama dokumen.
        expect($respons->getContent())->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');

        // Bukan collect(): SimpleXMLElement memakai nama elemen sebagai kunci sehingga hanya <url> terakhir yang tersisa.
        $url = [];
        foreach (simplexml_load_string($respons->getContent())->url as $item) {
            $url[(string) $item->loc] = (string) $item->lastmod;
        }

        // Kategori urut abjad; lastmod = perubahan terakhir kategorinya (nama) atau artikel terbitnya.
        expect($url)->toBe([
            $this->akar => '',
            $this->akar.'/artikel' => '2026-10-05T08:30:00+07:00',
            $this->akar.'/artikel/kategori/berita' => '2026-10-08T09:00:00+07:00',
            $this->akar.'/artikel/kategori/teknologi' => '2026-10-01T10:00:00+07:00',
            $this->akar.'/artikel/artikel-baru' => '2026-10-05T08:30:00+07:00',
            $this->akar.'/artikel/artikel-lama' => '2026-10-01T10:00:00+07:00',
            // Daftar portofolio: lastmod = perubahan terakhir portofolio yang tampil; portofolio terbaru lebih dulu.
            $this->akar.'/portofolio' => '2026-10-03T08:00:00+07:00',
            $this->akar.'/portofolio/portofolio-baru' => '2026-10-03T08:00:00+07:00',
            $this->akar.'/portofolio/portofolio-lama' => '2026-09-20T08:00:00+07:00',
            // Hanya tutorial terbit yang punya materi. Halaman tutorial dan daftarnya ikut berubah bila materinya diubah;
            // materi urut sesuai tampilannya.
            $this->akar.'/tutorial' => '2026-10-04T09:00:00+07:00',
            // Kategori urut abjad, hanya yang punya tutorial tayang; lastmod = perubahan terakhir kategorinya (nama) atau
            // tutorialnya, termasuk materinya.
            $this->akar.'/tutorial/kategori/basis-data' => '2026-10-07T07:00:00+07:00',
            $this->akar.'/tutorial/kategori/pemrograman-web' => '2026-10-04T09:00:00+07:00',
            $this->akar.'/tutorial/tutorial-uji' => '2026-10-04T09:00:00+07:00',
            $this->akar.'/tutorial/tutorial-uji/materi-pertama' => '2026-10-04T09:00:00+07:00',
            $this->akar.'/tutorial/tutorial-uji/materi-kedua' => '2026-09-16T08:00:00+07:00',
            $this->akar.'/tutorial/tutorial-data' => '2026-09-03T08:00:00+07:00',
            $this->akar.'/tutorial/tutorial-data/materi-data' => '2026-09-03T08:00:00+07:00',
        ]);
    });

    it('tetap sah tanpa artikel terbit, portofolio, dan tutorial', function () {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        expect($xml->url)->toHaveCount(4)
            ->and((string) $xml->url[1]->loc)->toBe($this->akar.'/artikel')
            ->and(isset($xml->url[1]->lastmod))->toBeFalse()
            ->and((string) $xml->url[2]->loc)->toBe($this->akar.'/portofolio')
            ->and(isset($xml->url[2]->lastmod))->toBeFalse()
            ->and((string) $xml->url[3]->loc)->toBe($this->akar.'/tutorial')
            ->and(isset($xml->url[3]->lastmod))->toBeFalse();
    });
});

describe('robots.txt', function () {
    it('menolak panel admin dan menunjuk sitemap', function () {
        $respons = $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        expect($respons->getContent())->toBe(
            "User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: {$this->akar}/sitemap.xml\n"
        );
    });

    it('tidak menyebut sitemap bila pengindeksan ditutup', function () {
        config(['seo.indeks' => false]);

        expect($this->get('/robots.txt')->getContent())->toBe("User-agent: *\nDisallow: /admin\nDisallow: /login\n");
    });

    it('tidak tertimpa berkas statis di folder public', function () {
        // Server mendahulukan berkas statis, sehingga public/robots.txt akan membuat rute ini tidak terpakai.
        expect(file_exists(public_path('robots.txt')))->toBeFalse();
    });
});

describe('helper Seo', function () {
    it('membentuk alamat mutlak dari APP_URL', function () {
        expect(Seo::alamat())->toBe($this->akar)
            ->and(Seo::alamat('/'))->toBe($this->akar)
            ->and(Seo::alamat('artikel'))->toBe($this->akar.'/artikel')
            ->and(Seo::alamat('/artikel?page=2'))->toBe($this->akar.'/artikel?page=2')
            ->and(Seo::alamat('https://contoh.test/gambar.webp'))->toBe('https://contoh.test/gambar.webp');
    });

    it('membaca ukuran gambar unggahan dari alamatnya', function () {
        $path = simpanGambarUji('company-profile/artikel/ukuran.webp', 640, 480);
        Storage::disk('public')->put('company-profile/artikel/rusak.webp', 'bukan gambar');

        expect(Seo::ukuranGambar(Storage::disk('public')->url($path)))->toBe([640, 480])
            ->and(Seo::ukuranGambar(Seo::alamat(Storage::disk('public')->url($path))))->toBe([640, 480])
            ->and(Seo::ukuranGambar(Storage::disk('public')->url('company-profile/artikel/tidak-ada.webp')))->toBeNull()
            ->and(Seo::ukuranGambar(Storage::disk('public')->url('company-profile/artikel/rusak.webp')))->toBeNull()
            ->and(Seo::ukuranGambar('https://contoh.test/gambar.webp'))->toBeNull();
    });
});
