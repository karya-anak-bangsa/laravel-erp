<?php

use App\Support\FilterTanggal;

it('membaca tanggal berformat Y-m-d', function () {
    expect(FilterTanggal::parse('2026-10-07')?->format('Y-m-d H:i:s'))->toBe('2026-10-07 00:00:00');
});

it('mengabaikan nilai kosong, format lain, atau tanggal yang tidak ada', function (?string $nilai) {
    expect(FilterTanggal::parse($nilai))->toBeNull();
})->with([
    'null' => [null],
    'kosong' => [''],
    'format Indonesia' => ['07-10-2026'],
    'teks' => ['kemarin'],
    'tanggal 31 Februari' => ['2026-02-31'],
    'bulan 13' => ['2026-13-01'],
]);
