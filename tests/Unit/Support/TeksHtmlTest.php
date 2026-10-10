<?php

use App\Support\TeksHtml;

it('mengambil teks terlihat dari HTML dengan spasi antarblok', function () {
    expect(TeksHtml::polos('<p>Satu &amp; <strong>dua</strong></p><ul><li><p>tiga</p></li><li><p>empat</p></li></ul><p>a<br>b</p>'))
        ->toBe('Satu & dua tiga empat a b');
});

it('mengembalikan string kosong untuk HTML tanpa teks', function (?string $html) {
    expect(TeksHtml::polos($html))->toBe('');
})->with([null, '', '<p></p>', '<p><br></p>']);

it('membuang sub-judul dari ringkasan agar tidak menyambung dengan kalimat sesudahnya', function () {
    expect(TeksHtml::ringkasan('<p>Pembuka.</p><h2>Sub <strong>judul</strong></h2><p>Isi satu.</p><h3 class="x">Lagi</h3><p>Isi dua.</p>'))
        ->toBe('Pembuka. Isi satu. Isi dua.')
        ->and(TeksHtml::ringkasan(null))->toBe('');
});

it('mengubah teks polos menjadi paragraf HTML yang aman', function () {
    expect(TeksHtml::dariTeksPolos("Baris satu\nBaris dua & <b>\n\nParagraf dua"))
        ->toBe('<p>Baris satu<br>Baris dua &amp; &lt;b&gt;</p><p>Paragraf dua</p>');
});

it('membiarkan teks polos kosong atau null', function () {
    expect(TeksHtml::dariTeksPolos(null))->toBeNull()
        ->and(TeksHtml::dariTeksPolos(''))->toBe('');
});
