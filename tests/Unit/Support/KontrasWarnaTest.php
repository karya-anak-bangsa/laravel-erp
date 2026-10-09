<?php

use App\Support\KontrasWarna;

it('menghitung rasio kontras WCAG', function () {
    expect(KontrasWarna::rasio('#000000', '#ffffff'))->toEqualWithDelta(21.0, 0.001)
        ->and(KontrasWarna::rasio('#ffffff', '#ffffff'))->toEqualWithDelta(1.0, 0.001)
        ->and(KontrasWarna::rasio('#CB1839', '#ffffff'))->toBeGreaterThan(4.5);
});

it('membiarkan warna yang sudah terbaca di atas putih dan menyeragamkan huruf kecil', function () {
    expect(KontrasWarna::pastikanKontras('#CB1839'))->toBe('#cb1839')
        ->and(KontrasWarna::pastikanKontras('#15253F'))->toBe('#15253f');
});

it('menggelapkan warna terang sampai kontras minimal 4,5:1 sama seperti prototipe', function () {
    // Contoh di CATATAN prototipe: aksen kuning menjadi label seksi #9f6607.
    expect(KontrasWarna::pastikanKontras('#f59e0b'))->toBe('#9f6607');
});

it('selalu menghasilkan teks yang terbaca untuk warna terang', function (string $warna) {
    expect(KontrasWarna::rasio(KontrasWarna::pastikanKontras($warna), '#ffffff'))->toBeGreaterThanOrEqual(4.5);
})->with(['#ffffff', '#f59e0b', '#fde047', '#38bdf8', '#a3e635', '#f472b6']);

it('memilih teks putih atau gelap di atas tombol aksen', function () {
    expect(KontrasWarna::teksDiAtas('#CB1839'))->toBe('#ffffff')
        ->and(KontrasWarna::teksDiAtas('#f59e0b'))->toBe('#0f172a');
});

it('menolak warna yang bukan hex #rrggbb', function (string $warna) {
    KontrasWarna::normalisasi($warna);
})->with(['red', '#fff', '#12345g', '15253F', '#15253F;}'])->throws(InvalidArgumentException::class);
