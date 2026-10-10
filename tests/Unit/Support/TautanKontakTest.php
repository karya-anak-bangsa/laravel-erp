<?php

use App\Support\TautanKontak;

it('membuat tautan telepon berformat internasional', function (string $nomor) {
    expect(TautanKontak::telepon($nomor))->toBe('tel:+6281234567890');
})->with(['0812-3456-7890', '+62 812 3456 7890', '62 812-3456-7890']);

it('menampilkan nomor WhatsApp dalam format lokal', function (string $url, string $nomor) {
    expect(TautanKontak::tampilanWhatsapp($url))->toBe($nomor);
})->with([
    ['https://wa.me/6281234567890', '0812-3456-7890'],
    ['https://wa.me/6281234567890/', '0812-3456-7890'],
    ['https://wa.me/628123456789?text=Halo', '0812-3456-789'],
    ['https://api.whatsapp.com/send?phone=6281234567890&text=Halo', '0812-3456-7890'],
]);

it('menampilkan tautan WhatsApp tanpa skema bila nomornya tidak terbaca', function () {
    expect(TautanKontak::tampilanWhatsapp('https://wa.me/message/ABC123'))->toBe('wa.me/message/ABC123');
});

it('memberi titik patah baris setelah @ pada email tanpa membuka celah HTML', function () {
    expect((string) TautanKontak::emailBisaPatah('info@karyaanakbangsa.co.id'))->toBe('info@<wbr>karyaanakbangsa.co.id')
        ->and((string) TautanKontak::emailBisaPatah('<b>x</b>@a.id'))->toBe('&lt;b&gt;x&lt;/b&gt;@<wbr>a.id');
});
