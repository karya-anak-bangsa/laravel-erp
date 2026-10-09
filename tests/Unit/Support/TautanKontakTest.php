<?php

use App\Support\TautanKontak;

it('membuat tautan telepon berformat internasional', function (string $nomor) {
    expect(TautanKontak::telepon($nomor))->toBe('tel:+6281234567890');
})->with(['0812-3456-7890', '+62 812 3456 7890', '62 812-3456-7890']);

it('menampilkan tautan WhatsApp tanpa skema', function () {
    expect(TautanKontak::tampilanWhatsapp('https://wa.me/6281234567890'))->toBe('wa.me/6281234567890')
        ->and(TautanKontak::tampilanWhatsapp('https://wa.me/6281234567890/'))->toBe('wa.me/6281234567890');
});

it('memberi titik patah baris setelah @ pada email tanpa membuka celah HTML', function () {
    expect((string) TautanKontak::emailBisaPatah('info@karyaanakbangsa.co.id'))->toBe('info@<wbr>karyaanakbangsa.co.id')
        ->and((string) TautanKontak::emailBisaPatah('<b>x</b>@a.id'))->toBe('&lt;b&gt;x&lt;/b&gt;@<wbr>a.id');
});
