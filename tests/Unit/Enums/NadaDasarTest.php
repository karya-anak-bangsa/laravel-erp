<?php

use App\Enums\CompanyProfile\NadaDasar;
use App\Support\KontrasWarna;

/**
 * oklch(L% C H) → OKLab [L, a, b].
 *
 * @return array{float, float, float}
 */
function oklabNada(string $oklch): array
{
    preg_match('/^oklch\(([\d.]+)% ([\d.]+) ([\d.]+)\)$/', $oklch, $cocok);
    [$l, $c, $h] = [(float) $cocok[1] / 100, (float) $cocok[2], deg2rad((float) $cocok[3])];

    return [$l, $c * cos($h), $c * sin($h)];
}

/**
 * OKLab → hex sRGB 8-bit (dipotong ke gamut), seperti piksel yang tampil di layar.
 *
 * @param  array{float, float, float}  $lab
 */
function hexNada(array $lab): string
{
    [$l, $a, $b] = $lab;
    $kl = ($l + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $km = ($l - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $ks = ($l - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
    $linear = [
        4.0767416621 * $kl - 3.3077115913 * $km + 0.2309699292 * $ks,
        -1.2684380046 * $kl + 2.6097574011 * $km - 0.3413193965 * $ks,
        -0.0041960863 * $kl - 0.7034186147 * $km + 1.7076147010 * $ks,
    ];

    return '#'.implode('', array_map(function (float $v): string {
        $v = min(1, max(0, $v));

        return sprintf('%02x', (int) round(($v <= 0.0031308 ? 12.92 * $v : 1.055 * $v ** (1 / 2.4) - 0.055) * 255));
    }, $linear));
}

it('menyediakan sebelas tingkat oklch untuk setiap nada', function (NadaDasar $nada) {
    expect($nada->skala())->toHaveCount(count(NadaDasar::TINGKAT))
        ->each->toMatch('/^oklch\([\d.]+% [\d.]+ [\d.]+\)$/');
})->with(NadaDasar::cases());

it('mempertahankan Netral sama dengan Tailwind neutral (tampilan Monochrome yang disetujui)', function () {
    expect(NadaDasar::Netral->skala())->toBe([
        'oklch(98.5% 0 0)', 'oklch(97% 0 0)', 'oklch(92.2% 0 0)', 'oklch(87% 0 0)', 'oklch(70.8% 0 0)', 'oklch(55.6% 0 0)',
        'oklch(43.9% 0 0)', 'oklch(37.1% 0 0)', 'oklch(26.9% 0 0)', 'oklch(20.5% 0 0)', 'oklch(14.5% 0 0)',
    ]);
});

it('menjaga teks redup dan teks tombol tetap terbaca di setiap nada', function (NadaDasar $nada) {
    $skala = array_combine(NadaDasar::TINGKAT, $nada->skala());
    $teksRedup = hexNada(oklabNada($skala[500]));
    // --latar-redup (seksi portofolio & FAQ) = color-mix(in oklab, var(--nada-100) 40%, #fff), dicampur di OKLab seperti browser.
    $latarRedup = hexNada(array_map(fn (float $v, float $putih): float => 0.4 * $v + 0.6 * $putih, oklabNada($skala[100]), [1.0, 0.0, 0.0]));

    expect(KontrasWarna::rasio($teksRedup, '#ffffff'))->toBeGreaterThanOrEqual(4.5)
        ->and(KontrasWarna::rasio($teksRedup, $latarRedup))->toBeGreaterThanOrEqual(4.5)
        // Tombol utama: teks tingkat 50 di atas tingkat 900.
        ->and(KontrasWarna::rasio(hexNada(oklabNada($skala[50])), hexNada(oklabNada($skala[900]))))->toBeGreaterThanOrEqual(4.5);
})->with(NadaDasar::cases());

it('membuat setiap nada terlihat berbeda di band ajakan, footer, dan judul', function () {
    // Jarak OKLab di tingkat 950 minimal 0,03 (jelas terlihat). Zinc & Stone lama hanya 0,004–0,006 dari Netral,
    // sehingga pilihan nada dulu nyaris tidak mengubah tampilan (review pemilik 2026-10-14).
    $nada = NadaDasar::cases();

    foreach ($nada as $i => $a) {
        foreach (array_slice($nada, $i + 1) as $b) {
            $jarak = sqrt(array_sum(array_map(fn (float $x, float $y): float => ($x - $y) ** 2, oklabNada($a->skala()[10]), oklabNada($b->skala()[10]))));

            expect($jarak)->toBeGreaterThanOrEqual(0.03, "{$a->value} dan {$b->value} terlalu mirip");
        }
    }
});
