<?php

namespace App\Services\Shared;

use App\Support\PerataanTeksSanitizer;
use App\Support\TeksHtml;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerService
{
    // Kolom TEXT MySQL = 65.535 byte; utf8mb4 maks. 4 byte per karakter.
    public const PANJANG_HTML_MAKS = 16000;

    // Isi artikel disimpan di LONGTEXT; batas ini menjaga ukuran request & halaman tetap wajar.
    public const PANJANG_HTML_ARTIKEL_MAKS = 100000;

    private const PEMBUNGKUS = [
        'div', 'span', 'section', 'article', 'header', 'footer', 'main', 'font', 'small', 'big', 'mark', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'b', 'i', 's', 'strike', 'del', 'ins',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'dl', 'dt', 'dd', 'label',
    ];

    // Sub-judul hanya tersedia di editor isi artikel (<x-admin.form-editor :judul-bagian="true">).
    private const JUDUL_BAGIAN = ['h2', 'h3'];

    private readonly HtmlSanitizer $sanitizer;

    private readonly HtmlSanitizer $sanitizerJudulBagian;

    public function __construct()
    {
        $this->sanitizer = $this->buatSanitizer([], self::PANJANG_HTML_MAKS);
        $this->sanitizerJudulBagian = $this->buatSanitizer(self::JUDUL_BAGIAN, self::PANJANG_HTML_ARTIKEL_MAKS);
    }

    /**
     * HTML aman untuk disimpan. Isi tanpa teks terlihat (mis. editor kosong "<p></p>")
     * menjadi string kosong agar aturan required tetap bekerja.
     *
     * @param  bool  $judulBagian  izinkan sub-judul h2/h3 (isi artikel)
     */
    public function bersihkan(?string $html, bool $judulBagian = false): ?string
    {
        if ($html === null) {
            return null;
        }

        $sanitizer = $judulBagian ? $this->sanitizerJudulBagian : $this->sanitizer;
        $bersih = trim($sanitizer->sanitize($html));

        if (TeksHtml::polos($bersih) === '') {
            return '';
        }

        // Paragraf kosong di akhir (sisa baris baru di editor) tidak perlu disimpan.
        return (string) preg_replace('#(?:<p(?:\s[^>]*)?>(?:\s|&nbsp;|<br\s*/?>)*</p>)+$#u', '', $bersih);
    }

    /**
     * @param  list<string>  $judulBagian
     */
    private function buatSanitizer(array $judulBagian, int $panjangHtmlMaks): HtmlSanitizer
    {
        // Hanya tag yang bisa dibuat toolbar editor (resources/js/admin/editor.js); sisanya
        // (skrip, style, atribut on*, gambar) dibuang sebelum disimpan dan ditampilkan apa adanya.
        $config = (new HtmlSanitizerConfig)
            // style blok hanya untuk perataan teks; nilainya disaring PerataanTeksSanitizer.
            ->allowElement('p', ['style'])
            ->withAttributeSanitizer(new PerataanTeksSanitizer)
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('u')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a', ['href'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            // Tautan internal website (mis. /portofolio, #kontak) tetap boleh.
            ->allowRelativeLinks()
            // Input lebih panjang dipotong sanitizer; hasilnya tetap ditolak PanjangTeksHtml.
            ->withMaxInputLength($panjangHtmlMaks * 4);

        foreach ($judulBagian as $elemen) {
            $config = $config->allowElement($elemen, ['style']);
        }

        // Elemen lain dibuang beserta isinya; pembungkus umum hasil tempel (Word, web) cukup
        // dilepas tagnya agar teksnya tidak ikut hilang.
        foreach (array_diff(self::PEMBUNGKUS, $judulBagian) as $elemen) {
            $config = $config->blockElement($elemen);
        }

        return new HtmlSanitizer($config);
    }
}
