<?php

use App\Rules\PanjangTeksHtml;

/** Pesan galat aturan untuk $nilai; larik kosong bila lolos. */
function galatPanjangTeksHtml(PanjangTeksHtml $aturan, mixed $nilai): array
{
    $galat = [];
    $aturan->validate('deskripsi', $nilai, function (string $pesan) use (&$galat): void {
        $galat[] = $pesan;
    });

    return $galat;
}

it('menghitung batas dari teks yang terlihat, bukan dari markup HTML', function () {
    expect(galatPanjangTeksHtml(new PanjangTeksHtml(4), '<p><strong>Halo</strong></p>'))->toBe([])
        ->and(galatPanjangTeksHtml(new PanjangTeksHtml(3), '<p><strong>Halo</strong></p>'))
        ->toBe(['Kolom :attribute tidak boleh lebih dari 3 karakter.']);
});

it('menolak HTML yang melewati batas totalnya walau teks terlihatnya masih di bawah batas', function () {
    expect(galatPanjangTeksHtml(new PanjangTeksHtml(10, maksHtml: 20), '<p><strong><em>Halo</em></strong></p>'))
        ->toBe(['Format kolom :attribute terlalu banyak; kurangi tautan atau format teks.']);
});

it('melewati nilai yang bukan teks karena tipenya diperiksa aturan lain', function (mixed $nilai) {
    expect(galatPanjangTeksHtml(new PanjangTeksHtml(1, maksHtml: 1), $nilai))->toBe([]);
})->with([
    'kosong' => [null],
    'larik' => [['<p>Halo</p>']],
    'angka' => [12345],
]);
