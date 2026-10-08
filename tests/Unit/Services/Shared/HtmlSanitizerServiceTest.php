<?php

use App\Services\Shared\HtmlSanitizerService;

beforeEach(function () {
    $this->sanitizer = app(HtmlSanitizerService::class);
});

it('mempertahankan format yang tersedia di toolbar editor', function () {
    $html = '<p><strong>Tebal</strong> <em>miring</em> <u>garis</u><br>baris baru</p><ul><li><p>poin</p></li></ul><ol><li><p>nomor</p></li></ol>';

    // Sanitizer menulis <br> sebagai <br />; keduanya setara di HTML.
    expect($this->sanitizer->bersihkan($html))->toBe(str_replace('<br>', '<br />', $html));
});

it('membuang skrip, style, gambar, iframe, dan atribut berbahaya', function () {
    $hasil = $this->sanitizer->bersihkan(
        '<script>alert(1)</script><style>p{}</style><p onclick="x()" style="color:red" class="a">Aman</p><img src=x onerror=alert(1)><iframe src="https://x.id"></iframe>',
    );

    expect($hasil)->toBe('<p>Aman</p>');
});

it('hanya mengizinkan tautan http, https, mailto, dan tautan internal', function () {
    $hasil = $this->sanitizer->bersihkan(
        '<p><a href="javascript:alert(1)">a</a> <a href="https://karyaanakbangsa.co.id" target="_blank" rel="nofollow">b</a> '
        .'<a href="mailto:info@karyaanakbangsa.co.id">c</a> <a href="/portofolio">d</a> <a href="#kontak">e</a></p>',
    );

    expect($hasil)->not->toContain('javascript')
        ->not->toContain('target=')
        ->toContain('<a href="https://karyaanakbangsa.co.id">b</a>')
        // "@" di-encode &#64; (tetap dibaca browser sebagai @).
        ->toContain('<a href="mailto:info&#64;karyaanakbangsa.co.id">c</a>')
        ->toContain('<a href="/portofolio">d</a>')
        ->toContain('<a href="#kontak">e</a>');
});

it('melepas tag pembungkus hasil tempel tanpa menghilangkan teksnya', function () {
    expect($this->sanitizer->bersihkan('<div><h2>Judul</h2><span>Isi</span></div>'))->toBe('JudulIsi');
});

it('mengosongkan isi tanpa teks terlihat', function (string $html) {
    expect($this->sanitizer->bersihkan($html))->toBe('');
})->with(['<p></p>', '<p><br></p>', '<p>   </p>', '<img src="x">']);

it('membuang paragraf kosong di akhir isi', function () {
    expect($this->sanitizer->bersihkan('<ul><li><p>poin</p></li></ul><p></p><p><br></p>'))->toBe('<ul><li><p>poin</p></li></ul>');
});

it('membiarkan null tetap null', function () {
    expect($this->sanitizer->bersihkan(null))->toBeNull();
});
