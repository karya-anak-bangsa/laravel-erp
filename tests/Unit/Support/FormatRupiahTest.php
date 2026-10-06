<?php

use App\Support\FormatRupiah;

it('memformat nominal bulat dengan titik pemisah ribuan tanpa sen', function (int|float|string $nilai, string $hasil) {
    expect(FormatRupiah::format($nilai))->toBe($hasil);
})->with([
    'nol' => [0, 'Rp 0'],
    'ratusan' => [500, 'Rp 500'],
    'jutaan' => [1250000, 'Rp 1.250.000'],
    'string desimal dari database' => ['1250000.00', 'Rp 1.250.000'],
    'float bulat' => [2500000.0, 'Rp 2.500.000'],
    'batas DECIMAL(15,2)' => ['9999999999999.00', 'Rp 9.999.999.999.999'],
]);

it('menampilkan sen dengan koma bila nominal tidak bulat', function () {
    expect(FormatRupiah::format('1250000.50'))->toBe('Rp 1.250.000,50')
        ->and(FormatRupiah::format(0.05))->toBe('Rp 0,05');
});

it('menaruh tanda minus di depan untuk nominal negatif', function () {
    expect(FormatRupiah::format(-1250000))->toBe('-Rp 1.250.000');
});

it('menganggap null sebagai nol', function () {
    expect(FormatRupiah::format(null))->toBe('Rp 0');
});
