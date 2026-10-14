<?php

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\KetebalanJudul;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use App\Models\CompanyProfile\Template;
use App\Services\CompanyProfile\TemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

it('membaca dua template bawaan dari config selama belum ada template aktif di database', function () {
    Template::factory()->create(['warna_aksen' => '#0ea5e9']);
    $layanan = new TemaService;

    expect($layanan->templateAktif(JenisTemplate::FullColor))->toBe([
        'nama' => 'TKAB Full Color',
        'warna_utama' => '#15253f',
        'warna_aksen' => '#cb1839',
        'nada_dasar' => null,
        'font' => FontTemplate::Geist,
        'sudut' => SudutTemplate::Sedang,
        'skala' => SkalaTemplate::Ringkas,
        'lebar_konten' => LebarKonten::Lebar,
        'ketebalan_judul' => KetebalanJudul::Semibold,
        'bayangan' => BayanganTemplate::Halus,
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
            'ketebalan_judul' => KetebalanJudul::Semibold,
            'bayangan' => BayanganTemplate::Halus,
        ]);
});

it('membaca template aktif per jenis dari database', function () {
    Template::factory()->aktif()->create([
        'nama' => 'Biru Laut',
        'warna_utama' => '#0c4a6e',
        'warna_aksen' => '#0ea5e9',
        'font' => FontTemplate::Poppins,
        'ketebalan_judul' => KetebalanJudul::Extrabold,
    ]);
    Template::factory()->monochrome()->aktif()->create(['nada_dasar' => NadaDasar::Stone, 'sudut' => SudutTemplate::Tajam]);
    $layanan = new TemaService;

    expect($layanan->templateAktif(JenisTemplate::FullColor))
        ->toMatchArray(['nama' => 'Biru Laut', 'warna_utama' => '#0c4a6e', 'warna_aksen' => '#0ea5e9', 'font' => FontTemplate::Poppins, 'ketebalan_judul' => KetebalanJudul::Extrabold])
        ->and($layanan->templateAktif(JenisTemplate::Monochrome))
        ->toMatchArray(['nada_dasar' => NadaDasar::Stone, 'sudut' => SudutTemplate::Tajam])
        ->and($layanan->tokenCss())
        ->toContain('--warna-aksen: #0ea5e9;')
        ->toContain('--font-tema: "Poppins"; --ketebalan-judul: 800;')
        ->and($layanan->aliasFont())->toBe(['poppins', 'geist', 'geist-mono']);
});

it('membaca kedua template aktif dengan satu query per halaman', function () {
    Template::factory()->aktif()->create();
    Template::factory()->monochrome()->aktif()->create();
    $layanan = new TemaService;

    DB::enableQueryLog();
    $layanan->tokenCss();
    $layanan->aliasFont();

    expect(collect(DB::getQueryLog())->pluck('query')->filter(fn (string $sql) => str_contains($sql, 'tb_template')))->toHaveCount(1);
});

it('memakai config bila database tidak terbaca agar halaman tetap tampil', function () {
    Log::spy();
    config(['database.connections.rusak' => [...config('database.connections.mysql'), 'database' => 'laravel_erp_tidak_ada']]);
    $koneksiAsli = DB::getDefaultConnection();
    DB::setDefaultConnection('rusak');

    try {
        $template = (new TemaService)->templateAktif(JenisTemplate::FullColor);
    } finally {
        // Rollback RefreshDatabase saat teardown memakai koneksi bawaan.
        DB::setDefaultConnection($koneksiAsli);
    }

    expect($template)->toMatchArray(['nama' => 'TKAB Full Color', 'warna_aksen' => '#cb1839']);
    Log::shouldHaveReceived('warning')->withArgs(fn (string $pesan) => str_contains($pesan, 'tidak terbaca dari database'))->once();
});

it('memakai template pratinjau untuk jenisnya dan menjadikannya tema halaman', function () {
    $pratinjau = new Template(['nama' => 'Coba Stone', 'jenis' => 'monochrome', 'nada_dasar' => 'stone', 'font' => 'inter', 'sudut' => 'bulat', 'skala' => 'lega', 'lebar_konten' => 'standar', 'ketebalan_judul' => 'bold', 'bayangan' => 'tegas']);
    $layanan = (new TemaService)->pratinjau($pratinjau);

    expect($layanan->jenisDariRequest(Request::create('/', cookies: ['tema' => 'full_color'])))->toBe(JenisTemplate::Monochrome)
        ->and($layanan->templateAktif(JenisTemplate::Monochrome))->toMatchArray(['nama' => 'Coba Stone', 'nada_dasar' => NadaDasar::Stone, 'font' => FontTemplate::Inter, 'bayangan' => BayanganTemplate::Tegas])
        ->and($layanan->templateAktif(JenisTemplate::FullColor))->toMatchArray(['nama' => 'TKAB Full Color'])
        ->and($layanan->aliasFont())->toBe(['geist', 'inter', 'geist-mono']);
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

/**
 * @param  array<string, string>  $token
 */
function deklarasiToken(array $token): string
{
    return implode(' ', array_map(fn (string $nama, string $nilai): string => "{$nama}: {$nilai};", array_keys($token), $token));
}

it('merender token lengkap kedua template', function () {
    $css = (new TemaService)->tokenCss();
    $bayanganWarna = deklarasiToken(BayanganTemplate::Halus->token(JenisTemplate::FullColor));
    $bayanganMono = deklarasiToken(BayanganTemplate::Halus->token(JenisTemplate::Monochrome));

    expect($css)
        ->toContain("html[data-tema=full_color] { --warna-utama: #15253f; --warna-aksen: #cb1839; --utama-teks: #15253f; --aksen-teks: #cb1839; --aksen-kontras: #ffffff; --font-tema: \"Geist\"; --ketebalan-judul: 600; --sudut-kartu: .75rem; --sudut-dalam: .5rem; --sudut-tombol: .5rem; --sudut-lencana: .375rem; --sudut-isian: .5rem; {$bayanganWarna} --tinggi-navbar: 4rem; --tinggi-logo: 1.75rem; --teks-menu: .875rem; --tinggi-tombol: 2.5rem; --teks-tombol: .875rem; --teks-judul-seksi: 2.25rem; --lebar-konten: 80rem; }")
        ->toContain('html[data-tema=monochrome] { --nada-50: oklch(98.5% 0 0);')
        ->toContain('--nada-950: oklch(14.5% 0 0); --font-tema: "Geist"; --ketebalan-judul: 600; --sudut-kartu: .75rem;')
        ->toContain("--sudut-isian: .5rem; {$bayanganMono} --tinggi-navbar: 4rem;")
        // Sebelas deklarasi skala nada; rujukan var(--nada-*) di --garis-kartu tidak dihitung.
        ->and(preg_match_all('/--nada-\d+:/', $css))->toBe(11);
});

it('merender bayangan sesuai pilihan masing-masing template', function () {
    config(['tema.template.full_color.bayangan' => 'tegas', 'tema.template.monochrome.bayangan' => 'tanpa']);

    // tokenCss(): baris 1 Full Color, baris 2 Monochrome; bayangan tidak berubah di layar lebar (baris 3).
    [$warna, $mono, $layarLebar] = explode("\n", (new TemaService)->tokenCss());

    expect($warna)->toContain(deklarasiToken(BayanganTemplate::Tegas->token(JenisTemplate::FullColor)))
        ->and($mono)->toContain(deklarasiToken(BayanganTemplate::Tanpa->token(JenisTemplate::Monochrome)))
        ->toContain('--bayangan-kartu: 0 0 #0000;')
        ->toContain('--garis-kartu: var(--nada-300);')
        ->and($layarLebar)->not->toContain('--bayangan-');
});

it('memakai bayangan bawaan tanpa menggugurkan nilai lain bila pilihannya tidak dikenal', function () {
    config(['tema.template.full_color.bayangan' => 'gelap', 'tema.template.full_color.warna_aksen' => '#f59e0b']);

    expect((new TemaService)->templateAktif(JenisTemplate::FullColor))
        ->toMatchArray(['bayangan' => BayanganTemplate::Halus, 'warna_aksen' => '#f59e0b']);
});

it('merender skala yang sama untuk kedua jenis dan menaikkannya satu tingkat di layar 1920px ke atas', function () {
    $skalaRingkas = '--tinggi-navbar: 4rem; --tinggi-logo: 1.75rem; --teks-menu: .875rem; --tinggi-tombol: 2.5rem; --teks-tombol: .875rem; --teks-judul-seksi: 2.25rem; --lebar-konten: 80rem; }';
    $skalaStandar = '{ --tinggi-navbar: 4.25rem; --tinggi-logo: 1.875rem; --teks-menu: .875rem; --tinggi-tombol: 2.75rem; --teks-tombol: .875rem; --teks-judul-seksi: 2.375rem;';
    $css = (new TemaService)->tokenCss();

    expect(substr_count($css, $skalaRingkas))->toBe(2)
        ->and($css)
        ->toContain("@media (min-width: 1920px) { html[data-tema=full_color] {$skalaStandar}")
        ->toContain("html[data-tema=monochrome] {$skalaStandar}");
});

it('memberi ukuran identik bila kedua template memilih skala yang sama', function (SkalaTemplate $skala) {
    config(['tema.template.full_color.skala' => $skala->value, 'tema.template.monochrome.skala' => $skala->value]);
    $deklarasi = fn (SkalaTemplate $tingkat): string => vsprintf(
        '--tinggi-navbar: %s; --tinggi-logo: %s; --teks-menu: %s; --tinggi-tombol: %s; --teks-tombol: %s; --teks-judul-seksi: %s;',
        array_values($tingkat->token()),
    );

    // tokenCss(): baris 1 Full Color, baris 2 Monochrome, baris 3 blok ≥1920px (kedua jenis, satu tingkat lebih besar).
    [$dasarWarna, $dasarMono, $layarLebar] = explode("\n", (new TemaService)->tokenCss());

    expect($dasarWarna)->toContain($deklarasi($skala))
        ->and($dasarMono)->toContain($deklarasi($skala))
        ->and(substr_count($layarLebar, $deklarasi($skala->naik())))->toBe(2);
})->with(SkalaTemplate::cases());

it('melebarkan konten di layar 1920px ke atas sesuai pilihan template', function () {
    config(['tema.template.monochrome.lebar_konten' => 'standar']);

    expect((new TemaService)->tokenCss())
        ->toContain('--teks-judul-seksi: 2.375rem; --lebar-konten: 90rem; } html[data-tema=monochrome]')
        ->toContain('--teks-judul-seksi: 2.375rem; --lebar-konten: 80rem; } }');
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

it('memuat font kedua template beserta Geist Mono tanpa duplikat', function () {
    expect((new TemaService)->aliasFont())->toBe(['geist', 'geist-mono']);
});
