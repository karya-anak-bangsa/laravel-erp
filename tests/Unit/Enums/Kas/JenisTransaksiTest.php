<?php

use App\Enums\Kas\JenisTransaksi;

it('menyediakan opsi select berurutan dengan label berbahasa Indonesia', function () {
    expect(JenisTransaksi::opsi())->toBe([
        'pemasukan' => 'Pemasukan',
        'pengeluaran' => 'Pengeluaran',
    ]);
});

it('membedakan pemasukan dan pengeluaran dengan warna chip yang tersedia di Gentelella', function () {
    expect(JenisTransaksi::Pemasukan->warna())->toBe('green')
        ->and(JenisTransaksi::Pengeluaran->warna())->toBe('red');
});
