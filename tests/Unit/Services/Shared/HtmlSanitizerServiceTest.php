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

it('mempertahankan perataan teks paragraf dari toolbar editor', function () {
    $html = '<p style="text-align: center">tengah</p><p style="text-align: right">kanan</p>'
        .'<ul><li><p style="text-align: justify">rata kanan-kiri</p></li></ul>';

    expect($this->sanitizer->bersihkan($html))->toBe($html)
        // Penulisan lain dinormalkan agar tersimpan seragam.
        ->and($this->sanitizer->bersihkan('<p style="TEXT-ALIGN:Justify;">isi</p>'))->toBe('<p style="text-align: justify">isi</p>');
});

it('membuang style selain perataan teks yang diizinkan', function (string $style) {
    expect($this->sanitizer->bersihkan('<p style="'.$style.'">isi</p>'))->toBe('<p>isi</p>');
})->with([
    'rata kiri bawaan' => 'text-align: left',
    'nilai tidak dikenal' => 'text-align: start',
    'perataan bercampur CSS lain' => 'text-align: center; color: red',
    'CSS lain' => 'background: url(https://x.id/a.png)',
]);

it('membuang style perataan pada elemen selain paragraf', function () {
    expect($this->sanitizer->bersihkan('<p><strong style="text-align: center">tebal</strong></p>'))->toBe('<p><strong>tebal</strong></p>');
});

it('melepas tag sub-judul di editor biasa tetapi mempertahankannya di isi artikel', function () {
    $html = '<h2 style="text-align: center">Bagian</h2><h3>Sub</h3><p>isi</p>';

    expect($this->sanitizer->bersihkan($html))->toBe('BagianSub<p>isi</p>')
        ->and($this->sanitizer->bersihkan($html, judulBagian: true))->toBe($html)
        // h1 & h4–h6 tidak tersedia di toolbar sehingga tetap dilepas tagnya.
        ->and($this->sanitizer->bersihkan('<h1>A</h1><h4>B</h4>', judulBagian: true))->toBe('AB');
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
    expect($this->sanitizer->bersihkan('<ul><li><p>poin</p></li></ul><p></p><p style="text-align: center"><br></p>'))->toBe('<ul><li><p>poin</p></li></ul>');
});

it('membiarkan null tetap null', function () {
    expect($this->sanitizer->bersihkan(null))->toBeNull();
});
