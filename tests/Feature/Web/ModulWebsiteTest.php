<?php

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Identitas::factory()->create(['link_whatsapp' => 'https://wa.me/6281200000000']);

    // Semua modul berdata agar setiap menu, seksi, dan halaman benar-benar bisa tampil.
    Layanan::factory()->create(['judul' => 'Layanan Uji']);
    $this->portofolio = Portofolio::factory()->create();
    $this->artikel = Artikel::factory()->terbit()->create();
    Faq::factory()->create();
    $this->tutorial = Tutorial::factory()->terbit()->create();
    $this->materi = MateriTutorial::factory()->for($this->tutorial)->create();
});

function nonaktifkanModul(JenisModul ...$daftarJenis): void
{
    foreach ($daftarJenis as $jenis) {
        ModulWebsite::factory()->nonaktif()->create(['jenis' => $jenis]);
    }
}

function alamatMenuModul(JenisModul $jenis): string
{
    return match ($jenis) {
        JenisModul::Layanan => route('beranda').'#layanan',
        JenisModul::Portofolio => route('portofolio.index'),
        JenisModul::Tutorial => route('tutorial.index'),
        JenisModul::Artikel => route('artikel.index'),
        JenisModul::Faq => route('beranda').'#faq',
        JenisModul::Ajakan => throw new LogicException('Ajakan tidak punya menu.'),
    };
}

it('menampilkan semua modul bila Manajemen Modul belum berisi data', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, 'class="tautan-menu"'))->toBe(6)
        ->and($html)->toContain('<a href="'.route('beranda').'#beranda" class="tautan-kaki">Beranda</a>')
        ->and($html)->toContain('<a href="'.route('beranda').'#faq" class="tautan-kaki">FAQ</a>');

    foreach (['layanan', 'portofolio', 'ajakan', 'artikel', 'faq'] as $seksi) {
        expect($html)->toContain('<section id="'.$seksi.'"');
    }
});

it('menyembunyikan menu navbar, Tautan Cepat, dan seksi beranda modul yang nonaktif', function (JenisModul $jenis) {
    nonaktifkanModul($jenis);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('href="'.alamatMenuModul($jenis).'"')
        ->and($html)->not->toContain('<section id="'.$jenis->value.'"')
        // Modul lain tetap tampil.
        ->and(substr_count($html, 'class="tautan-menu"'))->toBe(5)
        ->and(substr_count($html, 'class="tautan-kaki">'.$jenis->label().'</a>'))->toBe(0);
})->with([JenisModul::Layanan, JenisModul::Portofolio, JenisModul::Tutorial, JenisModul::Artikel, JenisModul::Faq]);

it('menghitung ulang latar seksi beranda dari seksi yang tampil', function () {
    expect($this->get('/')->getContent())
        ->toContain('<section id="layanan" class="seksi"')
        ->toContain('<section id="portofolio" class="seksi seksi-redup"')
        ->toContain('<section id="artikel" class="seksi"')
        ->toContain('<section id="faq" class="seksi seksi-redup"');

    nonaktifkanModul(JenisModul::Portofolio);

    // Tanpa Portofolio, Artikel menjadi seksi kedua sehingga redup, dan FAQ tidak lagi bersebelahan dengan seksi redup.
    expect($this->get('/')->getContent())
        ->toContain('<section id="layanan" class="seksi"')
        ->toContain('<section id="artikel" class="seksi seksi-redup"')
        ->toContain('<section id="faq" class="seksi"');
});

it('menjadikan halaman modul yang nonaktif 404', function (JenisModul $jenis, Closure $alamat) {
    $alamatHalaman = $alamat->call($this);
    $this->get($alamatHalaman)->assertOk();

    nonaktifkanModul($jenis);

    $this->get($alamatHalaman)->assertNotFound();
})->with([
    'daftar portofolio' => [JenisModul::Portofolio, fn () => route('portofolio.index')],
    'detail portofolio' => [JenisModul::Portofolio, fn () => route('portofolio.show', $this->portofolio)],
    'daftar artikel' => [JenisModul::Artikel, fn () => route('artikel.index')],
    'detail artikel' => [JenisModul::Artikel, fn () => route('artikel.show', $this->artikel)],
    'kategori artikel' => [JenisModul::Artikel, fn () => route('kategori-artikel.show', $this->artikel->kategoriArtikel)],
    'daftar tutorial' => [JenisModul::Tutorial, fn () => route('tutorial.index')],
    'halaman tutorial' => [JenisModul::Tutorial, fn () => route('tutorial.show', $this->tutorial)],
    'kategori tutorial' => [JenisModul::Tutorial, fn () => route('kategori-tutorial.show', $this->tutorial->kategoriTutorial)],
    'materi tutorial' => [JenisModul::Tutorial, fn () => route('materi-tutorial.show', [$this->tutorial, $this->materi])],
]);

it('tidak memasukkan halaman modul yang nonaktif ke sitemap', function () {
    nonaktifkanModul(JenisModul::Artikel, JenisModul::Tutorial);

    expect($this->get(route('sitemap'))->assertOk()->getContent())
        ->toContain('<loc>'.route('portofolio.index').'</loc>')
        ->toContain('<loc>'.route('portofolio.show', $this->portofolio).'</loc>')
        ->not->toContain('/artikel')
        ->not->toContain('/tutorial');
});

it('menyembunyikan kolom Layanan di footer dan kotak Layanan kami bila modul Layanan nonaktif', function () {
    $this->get(route('artikel.index'))
        ->assertSee('id="judul-kaki-layanan"', false)
        ->assertSee('id="judul-layanan-kami"', false);

    nonaktifkanModul(JenisModul::Layanan);

    $this->get(route('artikel.index'))
        ->assertOk()
        ->assertDontSee('id="judul-kaki-layanan"', false)
        ->assertDontSee('id="judul-layanan-kami"', false)
        ->assertDontSee('Layanan Uji');
});

it('menyembunyikan band ajakan di beranda dan halaman lain bila modul Ajakan nonaktif', function () {
    $this->get(route('artikel.index'))->assertSee('<section id="ajakan"', false);

    nonaktifkanModul(JenisModul::Ajakan);

    $this->get('/')->assertOk()->assertDontSee('<section id="ajakan"', false);
    $this->get(route('artikel.index'))->assertOk()->assertDontSee('<section id="ajakan"', false);
});

it('membuang tombol hero yang mengarah ke seksi beranda yang tidak dirender', function () {
    Hero::factory()->aktif()->create(['cta' => [
        ['label' => 'Lihat Portofolio', 'url' => '#portofolio', 'gaya' => 'primary'],
        ['label' => 'Pilih Layanan', 'url' => '#layanan-1', 'gaya' => 'secondary'],
        ['label' => 'Konsultasi', 'url' => 'https://wa.me/6281200000000', 'gaya' => 'secondary'],
    ]]);

    $this->get('/')->assertSeeInOrder(['Lihat Portofolio', 'Pilih Layanan', 'Konsultasi']);

    nonaktifkanModul(JenisModul::Portofolio, JenisModul::Layanan);

    $this->get('/')
        ->assertDontSee('Lihat Portofolio')
        ->assertDontSee('Pilih Layanan')
        ->assertSee('Konsultasi');
});

it('menampilkan navbar tanpa modul nonaktif di halaman 404', function () {
    nonaktifkanModul(JenisModul::Portofolio);

    expect($this->get('/alamat-tidak-ada')->assertNotFound()->getContent())
        ->toContain('class="tautan-menu"')
        ->not->toContain('href="'.route('portofolio.index').'"');
});
