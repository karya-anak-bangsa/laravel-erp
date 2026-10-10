<?php

use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use App\Services\CompanyProfile\TemaService;
use Illuminate\Http\Request;

it('membaca dua template bawaan dari config', function () {
    $layanan = new TemaService;

    expect($layanan->templateAktif(JenisTemplate::FullColor))->toBe([
        'nama' => 'TKAB Full Color',
        'warna_utama' => '#15253f',
        'warna_aksen' => '#cb1839',
        'nada_dasar' => null,
        'font' => FontTemplate::PlusJakartaSans,
        'sudut' => SudutTemplate::Sedang,
        'skala' => SkalaTemplate::Ringkas,
        'lebar_konten' => LebarKonten::Lebar,
    ])
        ->and($layanan->templateAktif(JenisTemplate::Monochrome))->toBe([
            'nama' => 'TKAB Monochrome',
            'warna_utama' => null,
            'warna_aksen' => null,
            'nada_dasar' => NadaDasar::Netral,
            'font' => FontTemplate::Geist,
            'sudut' => SudutTemplate::Sedang,
            'skala' => SkalaTemplate::Ringkas,
            'lebar_konten' => LebarKonten::Lebar,
        ]);
});

it('menentukan tema dari cookie pengunjung', function (mixed $cookie, JenisTemplate $harapan) {
    $request = Request::create('/', cookies: $cookie === null ? [] : ['tema' => $cookie]);

    expect((new TemaService)->jenisDariRequest($request))->toBe($harapan);
})->with([
    'monochrome' => ['monochrome', JenisTemplate::Monochrome],
    'full color' => ['full_color', JenisTemplate::FullColor],
    'tanpa cookie' => [null, JenisTemplate::FullColor],
    'tidak dikenal' => ['gelap', JenisTemplate::FullColor],
    'berbentuk array' => [['monochrome'], JenisTemplate::FullColor],
]);

it('merender token lengkap kedua template', function () {
    $css = (new TemaService)->tokenCss();

    expect($css)
        ->toContain('html[data-tema=full_color] { --warna-utama: #15253f; --warna-aksen: #cb1839; --utama-teks: #15253f; --aksen-teks: #cb1839; --aksen-kontras: #ffffff; --font-tema: "Plus Jakarta Sans"; --sudut-kartu: .75rem; --sudut-dalam: .5rem; --sudut-tombol: .5rem; --sudut-lencana: .375rem; --sudut-isian: .5rem; --tinggi-navbar: 4rem; --tinggi-logo: 2.25rem; --teks-menu: .875rem; --tinggi-tombol: 2.75rem; --teks-tombol: .9375rem; --lebar-konten: 80rem; }')
        ->toContain('html[data-tema=monochrome] { --nada-50: oklch(98.5% 0 0);')
        ->toContain('--nada-950: oklch(14.5% 0 0); --font-tema: "Geist"; --sudut-kartu: .75rem;')
        ->and(substr_count($css, '--nada-'))->toBe(11);
});

it('merender skala per jenis dan menaikkannya satu tingkat di layar 1920px ke atas', function () {
    expect((new TemaService)->tokenCss())
        ->toContain('--tinggi-navbar: 4rem; --tinggi-logo: 1.75rem; --teks-menu: .875rem; --tinggi-tombol: 2.5rem; --teks-tombol: .875rem; --lebar-konten: 80rem; }')
        ->toContain('@media (min-width: 1920px) { html[data-tema=full_color] { --tinggi-navbar: 4.5rem;')
        ->toContain('html[data-tema=monochrome] { --tinggi-navbar: 4.25rem;');
});

it('melebarkan konten di layar 1920px ke atas sesuai pilihan template', function () {
    config(['tema.template.monochrome.lebar_konten' => 'standar']);

    expect((new TemaService)->tokenCss())
        ->toContain('--teks-tombol: .9375rem; --lebar-konten: 90rem; } html[data-tema=monochrome]')
        ->toContain('--teks-tombol: .875rem; --lebar-konten: 80rem; } }');
});

it('membatasi kenaikan skala pada tingkat terbesar', function () {
    expect(SkalaTemplate::Ringkas->naik())->toBe(SkalaTemplate::Standar)
        ->and(SkalaTemplate::Lega->naik())->toBe(SkalaTemplate::Lega);
});

it('memakai skala bawaan tanpa menggugurkan nilai lain bila skala tidak ada di config', function () {
    config(['tema.template.full_color.skala' => null, 'tema.template.full_color.warna_aksen' => '#f59e0b']);

    expect((new TemaService)->templateAktif(JenisTemplate::FullColor))
        ->toMatchArray(['skala' => SkalaTemplate::Ringkas, 'warna_aksen' => '#f59e0b']);
});

it('menghitung turunan kontras dari warna template', function () {
    config(['tema.template.full_color.warna_aksen' => '#f59e0b', 'tema.template.full_color.sudut' => 'bulat']);

    expect((new TemaService)->tokenCss())
        ->toContain('--warna-aksen: #f59e0b; --utama-teks: #15253f; --aksen-teks: #9f6607; --aksen-kontras: #0f172a;')
        ->toContain('--sudut-tombol: 9999px;');
});

it('memakai template bawaan bila config tidak valid agar halaman tetap tampil', function (array $config) {
    config(['tema.template.full_color' => $config]);

    expect((new TemaService)->templateAktif(JenisTemplate::FullColor)['warna_aksen'])->toBe('#cb1839');
})->with([
    'hex salah ketik' => [['warna_utama' => '#15253F', 'warna_aksen' => 'merah', 'font' => 'plus_jakarta_sans', 'sudut' => 'sedang']],
    'font tidak dikenal' => [['warna_utama' => '#15253F', 'warna_aksen' => '#CB1839', 'font' => 'comic_sans', 'sudut' => 'sedang']],
    'kosong (cache config lama)' => [[]],
]);

it('memuat font kedua template beserta Geist Mono', function () {
    expect((new TemaService)->aliasFont())->toBe(['plus-jakarta-sans', 'geist', 'geist-mono']);
});
