<?php

use App\Http\Controllers\Web\CompanyProfile\ArtikelController;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
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

        $this->get('/artikel?page=2')->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel?page=2">', false);
        $this->get('/artikel?page=1')->assertSee('<link rel="canonical" href="'.$this->akar.'/artikel">', false);
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

        foreach (['/', '/artikel', '/artikel/kategori/kategori-terindeks', '/artikel/artikel-terindeks'] as $alamat) {
            $this->get($alamat)
                ->assertSee('<meta name="robots" content="max-image-preview:large">', false)
                ->assertDontSee('noindex', false);
        }
    });

    it('menandai semua halaman publik noindex bila pengindeksan ditutup', function () {
        config(['seo.indeks' => false]);
        Artikel::factory()->terbit()->for(KategoriArtikel::factory()->create(['slug' => 'kategori-tertutup']), 'kategoriArtikel')
            ->create(['slug' => 'artikel-tertutup']);

        foreach (['/', '/artikel', '/artikel/kategori/kategori-tertutup', '/artikel/artikel-tertutup'] as $alamat) {
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
    it('memakai gambar hero aktif beserta ukurannya di beranda dan daftar artikel', function () {
        Hero::factory()->aktif()->create([
            'judul' => 'Hero & Uji',
            'gambar' => simpanGambarUji('company-profile/hero/hero-uji.webp', 1200, 630),
        ]);

        foreach (['/', '/artikel'] as $alamat) {
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

    it('tidak bisa ditutup paksa oleh judul artikel', function () {
        Artikel::factory()->terbit()->create(['judul' => '</script><script>alert(1)</script>', 'slug' => 'judul-jahat']);

        $html = $this->get('/artikel/judul-jahat')->getContent();

        expect($html)->not->toContain('<script>alert(1)</script>')
            ->and(jsonLdHalaman($html)[0]['@graph'][0]['headline'])->toBe('</script><script>alert(1)</script>');
    });
});

describe('sitemap.xml', function () {
    it('memuat beranda, daftar artikel, kategori berartikel terbit, dan artikel terbit saja', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Teknologi', 'slug' => 'teknologi', 'updated_at' => '2026-09-01 07:00:00']);
        $berita = KategoriArtikel::factory()->create(['nama_kategori' => 'Berita', 'slug' => 'berita', 'updated_at' => '2026-10-08 09:00:00']);
        $hanyaDraf = KategoriArtikel::factory()->create(['slug' => 'hanya-draf']);
        $terhapus = KategoriArtikel::factory()->create(['slug' => 'kategori-terhapus']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['slug' => 'artikel-lama', 'tanggal' => '2026-09-01', 'updated_at' => '2026-10-01 10:00:00']);
        Artikel::factory()->terbit()->for($berita, 'kategoriArtikel')->create(['slug' => 'artikel-baru', 'tanggal' => '2026-10-01', 'updated_at' => '2026-10-05 08:30:00']);
        Artikel::factory()->draf()->for($hanyaDraf, 'kategoriArtikel')->create(['slug' => 'artikel-draf']);
        Artikel::factory()->terbit()->for($terhapus, 'kategoriArtikel')->create(['slug' => 'artikel-terhapus'])->delete();

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
        ]);
    });

    it('tetap sah tanpa artikel terbit', function () {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        expect($xml->url)->toHaveCount(2)
            ->and((string) $xml->url[1]->loc)->toBe($this->akar.'/artikel')
            ->and(isset($xml->url[1]->lastmod))->toBeFalse();
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
