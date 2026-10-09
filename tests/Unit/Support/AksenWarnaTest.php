<?php

use App\Support\AksenWarna;

it('memberi corak tetap untuk kategori layanan perusahaan', function (string $kategori, string $corak) {
    expect(AksenWarna::untuk($kategori))->toBe($corak);
})->with([
    ['Website', 'corak-sky'],
    ['Mobile Apps', 'corak-indigo'],
    ['Pelatihan IT', 'corak-teal'],
    ['sertifikasi it', 'corak-violet'],
    ['Bootcamp', 'corak-rose'],
]);

it('memberi corak yang stabil untuk kategori lain', function () {
    $corak = AksenWarna::untuk('Desain Grafis');

    expect($corak)->toBe(AksenWarna::untuk('desain grafis'))
        ->and(substr($corak, strlen('corak-')))->toBeIn(AksenWarna::CORAK);
});
