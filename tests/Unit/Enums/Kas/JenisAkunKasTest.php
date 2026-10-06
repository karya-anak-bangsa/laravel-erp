<?php

use App\Enums\Kas\JenisAkunKas;

it('menyediakan opsi select berurutan dengan label berbahasa Indonesia', function () {
    expect(JenisAkunKas::opsi())->toBe([
        'tunai' => 'Tunai',
        'bank' => 'Bank',
        'e_wallet' => 'E-Wallet',
    ]);
});

it('memetakan setiap jenis ke warna chip yang tersedia di Gentelella', function () {
    foreach (JenisAkunKas::cases() as $jenis) {
        expect($jenis->warna())->toBeIn(['primary', 'blue', 'green', 'yellow', 'red', 'purple']);
    }
});
