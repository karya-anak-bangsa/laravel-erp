<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use Database\Seeders\KategoriPortofolioSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->identitas = Identitas::factory()->create([
        'nama_perusahaan' => 'PT Uji Beranda',
        'judul_website' => 'Situs Uji Beranda',
        'link_whatsapp' => 'https://wa.me/6281200000000',
    ]);
});

it('menampilkan beranda untuk tamu maupun admin yang sedang login', function () {
    $this->get(route('beranda'))
        ->assertOk()
        ->assertSee('<title>Situs Uji Beranda</title>', false)
        ->assertSee('PT Uji Beranda');

    $this->actingAs(Pengguna::factory()->create())->get('/')->assertOk();
});

describe('tema', function () {
    it('memakai Full Color bila pengunjung belum memilih tema', function () {
        $this->get('/')
            ->assertSee('<html lang="id" data-tema="full_color">', false)
            ->assertSee('data-pilih-tema="full_color" aria-pressed="true"', false);
    });

    it('memakai tema dari cookie pilihan pengunjung', function () {
        // Cookie ditulis JavaScript tanpa enkripsi, jadi wajib withUnencryptedCookie (withCookie() mengenkripsinya).
        $this->withUnencryptedCookie('tema', 'monochrome')
            ->get('/')
            ->assertSee('<html lang="id" data-tema="monochrome">', false)
            ->assertSee('data-pilih-tema="monochrome" aria-pressed="true"', false);
    });

    it('mengabaikan isi cookie tema yang tidak dikenal', function (string $nilai) {
        $this->withUnencryptedCookie('tema', $nilai)
            ->get('/')
            ->assertSee('data-tema="full_color"', false)
            ->assertDontSee($nilai, false);
    })->with(['gelap', '"><script>alert(1)</script>']);

    it('merender token kedua template aktif', function () {
        $respons = $this->get('/')
            ->assertSee('<style id="token-template"', false)
            ->assertSee('--warna-utama: #15253f; --warna-aksen: #cb1839;', false)
            ->assertSee('--aksen-kontras: #ffffff;', false)
            ->assertSee('--nada-950: oklch(14.5% 0.042 55);', false)
            ->assertSee('--sudut-tombol: .5rem;', false);

        // Kedua template memakai Geist (pilihan pemilik 2026-10-11).
        expect(substr_count($respons->getContent(), '--font-tema: "Geist";'))->toBe(2);
    });
});

describe('isi', function () {
    it('hanya menampilkan hero yang aktif', function () {
        Hero::factory()->create(['judul' => 'Hero Nonaktif']);
        Hero::factory()->aktif()->create(['judul' => 'Hero Aktif', 'keyword' => ['Kata Kunci Uji']]);
        Hero::factory()->aktif()->create(['judul' => 'Hero Terhapus'])->delete();

        $this->get('/')
            ->assertSee('Hero Aktif')
            ->assertSee('Kata Kunci Uji')
            ->assertDontSee('Hero Nonaktif')
            ->assertDontSee('Hero Terhapus');
    });

    it('tetap tampil tanpa hero aktif dengan judul website dan ajakan WhatsApp', function () {
        Hero::factory()->create(['judul' => 'Hero Nonaktif']);

        $respons = $this->get('/')
            ->assertOk()
            ->assertSee('<h1 id="judul-hero" class="hero-judul muncul tunda-1">Situs Uji Beranda</h1>', false)
            ->assertDontSee('Hero Nonaktif');

        expect($respons->getContent())
            ->toMatch('#<a href="https://wa\.me/6281200000000"\s+target="_blank" rel="noopener"\s+class="btn btn-besar"\s*>\s*Hubungi Kami#');
    });

    it('membuka CTA hero ke situs lain di tab baru', function () {
        Hero::factory()->aktif()->create(['cta' => [
            ['label' => 'Daftar Sekarang', 'url' => 'https://contoh.test/daftar', 'gaya' => 'secondary'],
        ]]);

        expect($this->get('/')->getContent())
            ->toMatch('#<a href="https://contoh\.test/daftar"\s+target="_blank" rel="noopener"\s+class="btn btn-besar"\s+data-variant="outline"\s*>#');
    });

    it('menampilkan layanan dan FAQ sesuai urutan tampil', function () {
        Layanan::factory()->create(['judul' => 'Layanan Kedua', 'urutan_ke' => 2]);
        Layanan::factory()->create(['judul' => 'Layanan Pertama', 'urutan_ke' => 1]);
        Faq::factory()->create(['pertanyaan' => 'Pertanyaan kedua?', 'urutan_ke' => 2]);
        Faq::factory()->create(['pertanyaan' => 'Pertanyaan pertama?', 'urutan_ke' => 1]);

        $this->get('/')
            ->assertSeeInOrder(['Layanan Pertama', 'Layanan Kedua'])
            ->assertSeeInOrder(['Pertanyaan pertama?', 'Pertanyaan kedua?']);
    });

    it('menyediakan modal detail hanya bila ada layanan berketerangan', function () {
        Layanan::factory()->create(['keterangan' => null]);
        $this->get('/')->assertDontSee('id="dialog-layanan"', false)->assertDontSee('data-buka-layanan', false);

        Layanan::factory()->create(['keterangan' => '<p>Rincian layanan</p>']);
        $this->get('/')
            ->assertSee('id="dialog-layanan"', false)
            ->assertSee('data-wa="https://wa.me/6281200000000"', false)
            ->assertSee('<div class="isi-layanan-rinci konten-html"><p>Rincian layanan</p></div>', false);
    });

    it('hanya menampilkan tiga artikel terbit terbaru beserta kategorinya', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Kategori Uji']);
        foreach (['2026-10-01' => 'Artikel Satu', '2026-10-02' => 'Artikel Dua', '2026-10-03' => 'Artikel Tiga', '2026-10-04' => 'Artikel Empat'] as $tanggal => $judul) {
            Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => $judul, 'tanggal' => $tanggal]);
        }
        Artikel::factory()->draf()->create(['judul' => 'Artikel Draf', 'tanggal' => '2026-10-05']);

        $this->get('/')
            ->assertSeeInOrder(['Artikel Empat', 'Artikel Tiga', 'Artikel Dua'])
            ->assertSee('Kategori Uji')
            ->assertSee('Diterbitkan <span class="whitespace-nowrap">4 Oktober 2026</span>', false)
            ->assertDontSee('04 Oktober 2026')
            ->assertDontSee('Artikel Satu')
            ->assertDontSee('Artikel Draf');
    });

    it('menampilkan artikel yang kategorinya sudah dihapus tanpa lencana kategori', function () {
        $kategori = KategoriArtikel::factory()->create(['nama_kategori' => 'Kategori Terhapus']);
        Artikel::factory()->terbit()->for($kategori, 'kategoriArtikel')->create(['judul' => 'Artikel Yatim']);
        $kategori->delete();

        $this->get('/')->assertOk()->assertSee('Artikel Yatim')->assertDontSee('Kategori Terhapus');
    });

    it('memotong ringkasan artikel di akhir kata sebelum 150 karakter', function () {
        // Karakter ke-150 jatuh di tengah kata ke-19, jadi ringkasan berhenti setelah kata ke-18.
        Artikel::factory()->terbit()->create(['deskripsi' => '<p>'.str_repeat('abcdefg ', 25).'</p>']);

        $this->get('/')->assertSee('<p class="kartu-artikel-ringkasan">'.rtrim(str_repeat('abcdefg ', 18)).'...</p>', false);
    });

    it('menampilkan ringkasan artikel sebagai teks polos tanpa sub-judul', function () {
        Artikel::factory()->terbit()->create(['deskripsi' => '<p>Pembuka.</p><h2>Sub-judul</h2><p>Isi <strong>tebal</strong> artikel.</p>']);

        $this->get('/')
            ->assertSee('<p class="kartu-artikel-ringkasan">Pembuka. Isi tebal artikel.</p>', false)
            ->assertDontSee('Sub-judul');
    });

    it('menampilkan enam portofolio terbaru menurut urutan kategori dibuat', function () {
        // Kategori awal mengikuti urutan layanan; kategori yang ditambahkan admin di belakangnya.
        $this->seed(KategoriPortofolioSeeder::class);
        Portofolio::factory()->kategori('Kategori Lama')->create(['judul' => 'Portofolio Terlama', 'created_at' => now()->subDays(10)]);
        foreach ([
            ['Proyek Bootcamp', 'Bootcamp'],
            ['Proyek Website Baru', 'Website'],
            ['Proyek Lain', 'Kategori Lain'],
            ['Proyek Mobile', 'Mobile Apps'],
            ['Proyek Website Lama', 'Website'],
            ['Proyek Pelatihan', 'Pelatihan IT'],
        ] as $hari => [$judul, $kategori]) {
            Portofolio::factory()->kategori($kategori)->create(['judul' => $judul, 'created_at' => now()->subDays($hari + 1)]);
        }

        $this->get('/')
            ->assertSeeInOrder(['Proyek Website Baru', 'Proyek Website Lama', 'Proyek Mobile', 'Proyek Pelatihan', 'Proyek Bootcamp', 'Proyek Lain'])
            ->assertSee('<article class="kartu-portofolio corak-indigo">', false)
            ->assertDontSee('Portofolio Terlama');
    });

    it('menampilkan portofolio tanpa tab filter kategori', function () {
        Portofolio::factory()->kategori('Website')->create();
        Portofolio::factory()->kategori('Mobile Apps')->create();

        $this->get('/')
            ->assertSee('class="daftar-portofolio', false)
            ->assertDontSee('role="tablist"', false)
            ->assertDontSee('role="tabpanel"', false)
            ->assertDontSee('data-filter=', false);
    });

    it('tetap tampil saat semua konten kosong dan menyembunyikan seksi tanpa data', function () {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="layanan"', false)
            ->assertDontSee('id="portofolio"', false)
            ->assertDontSee('id="artikel"', false)
            ->assertDontSee('id="faq"', false)
            ->assertSee('id="ajakan"', false);
    });

    it('menyembunyikan semua tautan WhatsApp bila identitas tidak punya link WhatsApp', function () {
        $this->identitas->update(['link_whatsapp' => null]);
        Layanan::factory()->create(['keterangan' => '<p>Rincian</p>']);
        Faq::factory()->create();

        $this->get('/')
            ->assertDontSee('id="tombol-wa"', false)
            ->assertDontSee('wa.me')
            ->assertSee('data-wa=""', false)
            // Tanpa formulir kontak, tombol band ajakan, CTA hero cadangan, dan ajakan FAQ tidak punya tujuan lain sehingga disembunyikan.
            ->assertDontSee('aksi-ajakan', false)
            ->assertDontSee('hero-aksi', false)
            ->assertDontSee('Belum menemukan jawaban?')
            // Telepon jadi cadangan kontak WhatsApp di footer.
            ->assertSee('class="tautan-kaki">'.e($this->identitas->telepon[0]).'</a>', false);
    });

    it('menampilkan semua email di footer, sedangkan semua telepon hanya cadangan bila link WhatsApp kosong', function () {
        $this->identitas->update(['email' => ['info@tkab.id', 'cs@tkab.id'], 'telepon' => ['0812-1111-2222', '021 555 0000']]);

        expect($this->get('/')->getContent())
            ->toContain('<a href="mailto:info@tkab.id" class="tautan-kaki">info@<wbr>tkab.id</a>')
            ->toContain('<a href="mailto:cs@tkab.id" class="tautan-kaki">cs@<wbr>tkab.id</a>')
            ->not->toContain('class="tautan-kaki">0812-1111-2222</a>');

        $this->identitas->update(['link_whatsapp' => null]);

        expect($this->get('/')->getContent())
            ->toContain('<a href="tel:+6281211112222" class="tautan-kaki">0812-1111-2222</a>')
            ->toContain('<a href="tel:+62215550000" class="tautan-kaki">021 555 0000</a>');
    });

    it('menampilkan nomor WhatsApp berlogo di footer tanpa baris telepon dan tulisan WhatsApp terpisah', function () {
        $html = $this->get('/')->getContent();

        expect($html)
            ->toContain('<span class="sr-only">WhatsApp </span>0812-0000-0000')
            ->not->toContain('class="tautan-kaki">'.e($this->identitas->telepon[0]).'</a>')
            ->not->toMatch('#class="tautan-kaki">WhatsApp<#');
    });

    it('tidak memuat formulir kontak maupun menu Kontak di navbar dan footer', function () {
        // Modul Kontak Kami dihapus 2026-10-20: kontak lewat WhatsApp; alamat & email cukup di kolom Kontak footer.
        $this->get('/')
            ->assertDontSee('id="kontak"', false)
            ->assertDontSee('<form', false)
            ->assertDontSee('#kontak', false)
            ->assertDontSee('>Kontak</a>', false)
            ->assertSee('<h2 class="judul-kaki">Kontak</h2>', false);
    });

    it('mengajak menghubungi lewat WhatsApp di band ajakan dan seksi FAQ', function () {
        Faq::factory()->create();

        $respons = $this->get('/')
            ->assertSeeInOrder(['<section id="ajakan"', '<a href="https://wa.me/6281200000000" target="_blank" rel="noopener" class="btn btn-besar">', 'Chat via WhatsApp'], false);

        expect($respons->getContent())->toMatch('#Belum menemukan jawaban\? <a href="https://wa\.me/6281200000000" target="_blank" rel="noopener" class="tautan-teks whitespace-nowrap">'
            .'Hubungi kami melalui WhatsApp<span class="sr-only"> \(membuka tab baru\)</span></a>\s*<span class="whitespace-nowrap">dan tim kami akan membantu\.</span>#');
    });

    it('merender HTML editor yang sudah disanitasi dan meng-escape teks biasa', function () {
        Layanan::factory()->create(['judul' => '<script>alert(1)</script>', 'deskripsi' => '<p><strong>Tebal</strong></p>']);

        $this->get('/')
            ->assertSee('<p><strong>Tebal</strong></p>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });

    it('menautkan kartu artikel ke halaman detail tanpa tombol Lihat semua artikel', function () {
        Artikel::factory()->terbit()->create(['judul' => 'Artikel Bertautan', 'slug' => 'artikel-bertautan']);

        $this->get('/')
            ->assertSee('<h3 class="kartu-artikel-judul"><a href="'.route('artikel.show', 'artikel-bertautan').'">Artikel Bertautan</a></h3>', false)
            // Halaman daftar cukup dicapai lewat menu Artikel di navbar (pilihan pemilik 2026-10-15).
            ->assertSee('<a href="'.route('artikel.index').'" class="tautan-menu"', false)
            ->assertDontSee('Lihat semua artikel')
            ->assertDontSee('aksi-artikel', false)
            ->assertDontSee('kepala-artikel', false);
    });

    it('menautkan judul kartu portofolio ke detailnya dengan ringkasan deskripsi tanpa tombol Lihat semua portofolio', function () {
        Portofolio::factory()->create([
            'judul' => 'Portofolio Bertautan',
            'slug' => 'portofolio-bertautan',
            'deskripsi' => '<p>'.str_repeat('abcdefg ', 25).'</p>',
        ]);

        $this->get('/')
            ->assertSee('<h3 class="judul-portofolio"><a href="'.route('portofolio.show', 'portofolio-bertautan').'">Portofolio Bertautan</a></h3>', false)
            // Maksimal 150 karakter, dipotong di akhir kata seperti kartu artikel (pilihan pemilik 2026-10-18).
            ->assertSee('<p class="deskripsi-portofolio">'.rtrim(str_repeat('abcdefg ', 18)).'...</p>', false)
            // Halaman daftar cukup dicapai lewat menu Portofolio di navbar (pilihan pemilik 2026-10-18).
            ->assertSee('<a href="'.route('portofolio.index').'" class="tautan-menu"', false)
            ->assertDontSee('Lihat semua portofolio');
    });

    it('menampilkan tautan Instagram dan YouTube identitas di footer bila diisi', function () {
        $this->get('/')
            ->assertSee('href="'.$this->identitas->link_instagram.'"', false)
            ->assertSee('href="'.$this->identitas->link_youtube.'"', false);

        $this->identitas->update(['link_instagram' => null, 'link_youtube' => null]);

        $this->get('/')->assertDontSee('Instagram')->assertDontSee('YouTube');
    });
});

describe('cache identitas', function () {
    it('tidak membaca tabel identitas lagi pada kunjungan berikutnya', function () {
        $this->get('/')->assertOk();

        DB::enableQueryLog();
        $this->get('/')->assertOk()->assertSee('PT Uji Beranda');

        expect(collect(DB::getQueryLog())->pluck('query')->filter(fn (string $sql) => str_contains($sql, 'tb_identitas')))->toBeEmpty();
    });

    it('langsung menampilkan identitas yang baru diubah', function () {
        $this->get('/')->assertSee('PT Uji Beranda');

        $this->identitas->update(['nama_perusahaan' => 'PT Nama Baru']);

        $this->get('/')->assertSee('PT Nama Baru')->assertDontSee('PT Uji Beranda');
    });

    it('tetap utuh saat cache menyerialisasi nilai seperti store database produksi', function () {
        // Produksi: cache database + serializable_classes=false, objek Eloquent akan rusak saat dibaca ulang.
        config(['cache.stores.array.serialize' => true]);
        Cache::forgetDriver('array');

        $this->get('/')->assertOk();
        $this->get('/')->assertOk()->assertSee('PT Uji Beranda')->assertSee('mailto:'.$this->identitas->email[0]);
    });

    it('menampilkan 404 ringkas tanpa menyimpan kegagalan ke cache bila identitas belum ada', function () {
        $this->identitas->forceDelete();
        Cache::flush();

        // Halaman error publik tidak bisa memakai identitas, jadi tampil ringkas dengan nama cadangan.
        $this->get('/')
            ->assertNotFound()
            ->assertSee('class="logo-situs merek-ringkas"', false)
            ->assertSee(config('perusahaan.nama'));

        expect(Cache::has(Identitas::KUNCI_CACHE))->toBeFalse();
    });
});
