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

    // Semua modul berdata agar setiap menu dan seksi benar-benar bisa tampil.
    $this->layanan = Layanan::factory()->create(['judul' => 'Layanan Uji']);
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
        JenisModul::Portofolio => route('portofolio.index'),
        JenisModul::Tutorial => route('tutorial.index'),
        JenisModul::Artikel => route('artikel.index'),
        default => route('beranda').'#'.$jenis->value,
    };
}

/**
 * @return array{menu: list<string>, kaki: list<string>, seksi: list<string>}
 */
function susunanBeranda(string $html): array
{
    preg_match_all('#class="tautan-menu"[^>]*>([^<]+)</a>#', $html, $menu);
    preg_match('#id="judul-kaki-tautan".*?</ul>#s', $html, $tautanCepat);
    preg_match_all('#class="tautan-kaki">([^<]+)</a>#', $tautanCepat[0] ?? '', $kaki);
    preg_match_all('#<section id="([a-z]+)" class="([^"]+)"#', $html, $seksi, PREG_SET_ORDER);

    return [
        'menu' => $menu[1],
        'kaki' => $kaki[1],
        'seksi' => array_map(fn (array $s): string => $s[1].(str_contains($s[2], 'seksi-redup') ? ' (redup)' : ''), $seksi),
    ];
}

it('menampilkan semua modul seurut bawaan bila Manajemen Modul belum berisi data', function () {
    expect(susunanBeranda($this->get('/')->assertOk()->getContent()))->toBe([
        'menu' => ['Beranda', 'Layanan', 'Portofolio', 'Tutorial', 'Artikel', 'FAQ'],
        'kaki' => ['Beranda', 'Layanan', 'Portofolio', 'Tutorial', 'Artikel', 'FAQ'],
        'seksi' => ['beranda', 'layanan', 'portofolio (redup)', 'ajakan', 'artikel', 'faq (redup)'],
    ]);
});

it('menyusun menu dan seksi beranda menurut urutan Manajemen Modul', function () {
    foreach ([JenisModul::Faq, JenisModul::Artikel, JenisModul::Ajakan, JenisModul::Layanan, JenisModul::Portofolio, JenisModul::Tutorial] as $nomor => $jenis) {
        ModulWebsite::factory()->create(['jenis' => $jenis, 'urutan_ke' => $nomor + 1]);
    }

    // Hero tetap pertama; latar selang-seling mengikuti urutan baru, band ajakan tidak dihitung.
    expect(susunanBeranda($this->get('/')->getContent()))->toBe([
        'menu' => ['Beranda', 'FAQ', 'Artikel', 'Layanan', 'Portofolio', 'Tutorial'],
        'kaki' => ['Beranda', 'FAQ', 'Artikel', 'Layanan', 'Portofolio', 'Tutorial'],
        'seksi' => ['beranda', 'faq', 'artikel (redup)', 'ajakan', 'layanan', 'portofolio (redup)'],
    ]);

    // Menu yang sama di halaman lain.
    expect(susunanBeranda($this->get(route('artikel.index'))->getContent())['menu'])
        ->toBe(['Beranda', 'FAQ', 'Artikel', 'Layanan', 'Portofolio', 'Tutorial']);
});

it('menyembunyikan menu navbar, Tautan Cepat, dan seksi beranda modul yang nonaktif', function (JenisModul $jenis) {
    nonaktifkanModul($jenis);

    $html = $this->get('/')->assertOk()->getContent();
    $susunan = susunanBeranda($html);

    expect($html)->not->toContain('href="'.alamatMenuModul($jenis).'"')
        ->and($susunan['menu'])->not->toContain($jenis->label())->toHaveCount(5)
        ->and($susunan['kaki'])->not->toContain($jenis->label())->toHaveCount(5)
        ->and($html)->not->toContain('<section id="'.$jenis->value.'"');
})->with([JenisModul::Layanan, JenisModul::Portofolio, JenisModul::Tutorial, JenisModul::Artikel, JenisModul::Faq]);

it('menghitung ulang latar seksi beranda dari seksi yang tampil', function () {
    nonaktifkanModul(JenisModul::Portofolio);

    // Tanpa Portofolio, Artikel menjadi seksi kedua sehingga redup, dan FAQ tidak lagi bersebelahan dengan seksi redup.
    expect(susunanBeranda($this->get('/')->getContent())['seksi'])
        ->toBe(['beranda', 'layanan', 'ajakan', 'artikel (redup)', 'faq']);
});

it('hanya menyembunyikan seksi ajakan di beranda, band di halaman lain tetap tampil', function () {
    nonaktifkanModul(JenisModul::Ajakan);

    $this->get('/')->assertOk()->assertDontSee('<section id="ajakan"', false);
    $this->get(route('artikel.index'))->assertOk()->assertSee('<section id="ajakan"', false);
});

it('tetap membuka halaman modul yang nonaktif dan memasukkannya ke sitemap', function () {
    nonaktifkanModul(JenisModul::Portofolio, JenisModul::Artikel, JenisModul::Tutorial);

    $this->get(route('portofolio.show', $this->portofolio))->assertOk();
    $this->get(route('artikel.index'))->assertOk();
    $this->get(route('kategori-artikel.show', $this->artikel->kategoriArtikel))->assertOk();
    $this->get(route('materi-tutorial.show', [$this->tutorial, $this->materi]))->assertOk();

    expect($this->get(route('sitemap'))->getContent())
        ->toContain('<loc>'.route('portofolio.show', $this->portofolio).'</loc>')
        ->toContain('<loc>'.route('artikel.show', $this->artikel).'</loc>')
        ->toContain('<loc>'.route('tutorial.show', $this->tutorial).'</loc>');
});

it('menampilkan daftar layanan di footer dan kolom samping tanpa tautan selama seksi Layanan nonaktif', function () {
    $tautan = 'href="'.route('beranda').'#layanan-'.$this->layanan->id_layanan.'"';

    expect($this->get(route('artikel.index'))->getContent())
        ->toContain('<nav aria-labelledby="judul-kaki-layanan"')
        ->toContain('<nav class="kotak-samping" aria-labelledby="judul-layanan-kami"')
        ->toContain($tautan);

    nonaktifkanModul(JenisModul::Layanan);

    expect($this->get(route('artikel.index'))->assertOk()->getContent())
        ->toContain('<div aria-labelledby="judul-kaki-layanan"')
        ->toContain('<span class="tautan-kaki">Layanan Uji</span>')
        ->toContain('<div class="kotak-samping" aria-labelledby="judul-layanan-kami"')
        ->toContain('<span class="tautan-samping"><span>Layanan Uji</span></span>')
        ->not->toContain($tautan);
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

    expect(susunanBeranda($this->get('/alamat-tidak-ada')->assertNotFound()->getContent())['menu'])
        ->toBe(['Beranda', 'Layanan', 'Tutorial', 'Artikel', 'FAQ']);
});
