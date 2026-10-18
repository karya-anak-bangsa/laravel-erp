<?php

use App\Http\Controllers\Web\CompanyProfile\PortofolioController;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');
    // Judul halaman selain beranda berakhiran nama perusahaan, bukan Judul Website.
    Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Portofolio', 'judul_website' => 'Situs Uji Portofolio']);
});

describe('daftar portofolio', function () {
    it('menampilkan portofolio terbaru lebih dulu dengan judul bertautan ke detailnya', function () {
        Portofolio::factory()->create(['judul' => 'Proyek Lama', 'kategori' => 'Mobile Apps', 'created_at' => now()->subDay()]);
        Portofolio::factory()->create([
            'judul' => 'Proyek Baru',
            'slug' => 'proyek-baru',
            'kategori' => 'Website',
            'deskripsi' => '<p>Website profil dengan <strong>panel admin</strong>.</p>',
        ]);
        Portofolio::factory()->create(['judul' => 'Proyek Terhapus'])->delete();

        $this->get(route('portofolio.index'))
            ->assertOk()
            ->assertSee('<title>Portofolio — PT Uji Portofolio</title>', false)
            ->assertSeeInOrder(['<h1', 'Hasil karya kami', '</h1>'], false)
            ->assertSeeInOrder(['Proyek Baru', 'Proyek Lama'])
            // Kartu sama dengan beranda; di bawah h1 halaman, judulnya menjadi h2 dan hanya judul yang bertautan.
            ->assertSee('<article class="kartu-portofolio corak-sky">', false)
            ->assertSee('<span class="lencana-kategori"><span class="titik" aria-hidden="true"></span>Website</span>', false)
            ->assertSee('<h2 class="judul-portofolio"><a href="'.route('portofolio.show', 'proyek-baru').'">Proyek Baru</a></h2>', false)
            ->assertSee('<p class="deskripsi-portofolio">Website profil dengan panel admin.</p>', false)
            ->assertDontSee('Proyek Terhapus');
    });

    it('membagi daftar menjadi sepuluh portofolio per halaman', function () {
        foreach (range(1, PortofolioController::PER_HALAMAN + 1) as $nomor) {
            Portofolio::factory()->create([
                'judul' => sprintf('Proyek Urut %02d', $nomor),
                'created_at' => now()->subDays($nomor),
            ]);
        }

        $this->get(route('portofolio.index'))
            ->assertSee('Proyek Urut 10')
            ->assertDontSee('Proyek Urut 11')
            ->assertSee('rel="next"', false);

        $this->get(route('portofolio.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('<title>Portofolio (Halaman 2) — PT Uji Portofolio</title>', false)
            ->assertSee('Proyek Urut 11')
            ->assertDontSee('Proyek Urut 10')
            ->assertSee('rel="prev"', false);

        // Halaman di luar jangkauan bukan halaman kosong yang sah.
        $this->get(route('portofolio.index', ['page' => 3]))->assertNotFound();
    });

    it('menampilkan pesan bila belum ada portofolio', function () {
        $this->get(route('portofolio.index'))
            ->assertOk()
            ->assertSee('Belum ada portofolio. Silakan kunjungi lagi nanti.')
            ->assertDontSee('class="paginasi"', false);
    });
});

describe('detail portofolio', function () {
    it('menampilkan portofolio beserta kategori, gambar, deskripsi, dan meta halaman', function () {
        $portofolio = Portofolio::factory()->create([
            'judul' => 'Aplikasi Antrean Klinik',
            'slug' => 'aplikasi-antrean-klinik',
            'kategori' => 'Mobile Apps',
            'deskripsi' => '<p>Pasien mengambil antrean dari rumah.</p><ul><li><p>Notifikasi giliran</p></li></ul>',
        ]);

        $this->get('/portofolio/aplikasi-antrean-klinik')
            ->assertOk()
            ->assertSee('<title>Aplikasi Antrean Klinik — PT Uji Portofolio</title>', false)
            ->assertSee('<meta name="description" content="Pasien mengambil antrean dari rumah. Notifikasi giliran">', false)
            ->assertSee('id="judul-portofolio"', false)
            ->assertSee('class="judul-seksi">Aplikasi Antrean Klinik</h1>', false)
            // Kategori sebagai lencana di atas judul seperti di kartu, lalu gambar dan isi (pilihan pemilik 2026-10-18).
            ->assertSeeInOrder([
                '<span class="lencana-kategori corak-indigo"><span class="titik" aria-hidden="true"></span>Mobile Apps</span>',
                '<h1',
                '<div class="bingkai-artikel">',
                '<img src="'.$portofolio->gambar_url.'"',
                '<div class="isi-artikel konten-html"><p>Pasien mengambil antrean dari rumah.</p><ul><li><p>Notifikasi giliran</p></li></ul></div>',
            ], false)
            ->assertSee('href="'.route('portofolio.index').'">Portofolio</a>', false)
            ->assertDontSee('class="bagikan"', false);
    });

    it('menolak portofolio terhapus dan slug yang tidak dikenal', function () {
        Portofolio::factory()->create(['slug' => 'portofolio-terhapus'])->delete();

        $this->get('/portofolio/portofolio-terhapus')->assertNotFound();
        $this->get('/portofolio/tidak-ada')->assertNotFound();
    });

    it('menampilkan lima portofolio terbaru lainnya di kolom samping tanpa portofolio yang sedang dibuka', function () {
        Portofolio::factory()->create(['judul' => 'Proyek Dibuka', 'slug' => 'proyek-dibuka']);
        foreach (['Lain Satu', 'Lain Dua', 'Lain Tiga', 'Lain Empat', 'Lain Lima', 'Lain Enam'] as $urutan => $judul) {
            Portofolio::factory()->create(['judul' => $judul, 'slug' => Str::slug($judul), 'kategori' => 'Website', 'created_at' => now()->subDays(6 - $urutan)]);
        }
        Portofolio::factory()->create(['judul' => 'Lain Terhapus', 'created_at' => now()->subHour()])->delete();

        $this->get('/portofolio/proyek-dibuka')
            ->assertSee('<h2 class="judul-samping">Portofolio lainnya</h2>', false)
            ->assertSeeInOrder(['Lain Enam', 'Lain Lima', 'Lain Empat', 'Lain Tiga', 'Lain Dua'])
            ->assertSee('<a href="'.route('portofolio.show', 'lain-enam').'" class="tautan-terbaru">', false)
            ->assertSee('<span class="kategori-terbaru">Website</span>', false)
            ->assertDontSee('Lain Satu')
            ->assertDontSee('Lain Terhapus')
            ->assertDontSee('<span class="judul-terbaru">Proyek Dibuka</span>', false)
            ->assertSee('<a href="'.route('portofolio.index').'" class="btn btn-besar tombol-semua-portofolio">Lihat semua portofolio</a>', false);
    });

    it('tidak menampilkan portofolio lainnya bila hanya ada satu portofolio', function () {
        Portofolio::factory()->create(['slug' => 'satu-satunya']);

        $this->get('/portofolio/satu-satunya')
            ->assertOk()
            ->assertSee('<aside class="kolom-samping"', false)
            ->assertDontSee('Portofolio lainnya')
            ->assertDontSee('Lihat semua portofolio');
    });

    it('meng-escape judul portofolio', function () {
        Portofolio::factory()->create(['judul' => '<script>alert(1)</script>', 'slug' => 'judul-berbahaya']);

        $this->get('/portofolio/judul-berbahaya')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });
});

it('menampilkan kartu konsultasi WhatsApp dan layanan di kolom samping daftar & detail', function () {
    $identitas = Identitas::query()->sole();
    $layanan = Layanan::factory()->create(['judul' => 'Layanan Uji']);
    Portofolio::factory()->create(['slug' => 'portofolio-samping']);

    foreach ([route('portofolio.index'), '/portofolio/portofolio-samping'] as $alamat) {
        $this->get($alamat)
            ->assertSee('<h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>', false)
            ->assertSee('href="'.route('beranda').'#layanan-'.$layanan->id_layanan.'" class="tautan-samping"', false)
            // Tombol yang sama juga ada di band ajakan; urutan memastikan tombol kartu ada sebelum band.
            ->assertSeeInOrder(['<h2 class="judul-ajakan-samping">Konsultasi gratis</h2>', '<a href="'.$identitas->link_whatsapp.'" target="_blank" rel="noopener" class="btn btn-besar">', '<section id="ajakan"'], false);
    }
});

it('menandai menu Portofolio di navbar: halaman itu sendiri di daftar, bagiannya di detail', function () {
    Portofolio::factory()->create(['slug' => 'portofolio-aktif']);
    $menuPortofolio = fn (string $nilai): string => '#href="'.preg_quote(route('portofolio.index'), '#').'" class="tautan-menu"\s+aria-current="'.$nilai.'"\s*>Portofolio</a>#';

    expect($this->get(route('portofolio.index'))->getContent())->toMatch($menuPortofolio('page'));
    expect($this->get('/portofolio/portofolio-aktif')->getContent())->toMatch($menuPortofolio('true'));
    // Dari halaman lain, menu navbar dan footer menuju halaman portofolio tanpa penanda.
    expect($this->get(route('artikel.index'))->getContent())
        ->toContain('<a href="'.route('portofolio.index').'" class="tautan-menu" >Portofolio</a>')
        ->toContain('<a href="'.route('portofolio.index').'" class="tautan-kaki">Portofolio</a>');
});
