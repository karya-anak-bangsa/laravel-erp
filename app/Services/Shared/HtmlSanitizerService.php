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

    private const PEMBUNGKUS = [
        'div', 'span', 'section', 'article', 'header', 'footer', 'main', 'font', 'small', 'big', 'mark', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'b', 'i', 's', 'strike', 'del', 'ins',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'dl', 'dt', 'dd', 'label',
    ];

    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        // Hanya tag yang bisa dibuat toolbar editor (resources/js/admin/editor.js); sisanya
        // (skrip, style, atribut on*, gambar) dibuang sebelum disimpan dan ditampilkan apa adanya.
        $config = (new HtmlSanitizerConfig)
            // style paragraf hanya untuk perataan teks; nilainya disaring PerataanTeksSanitizer.
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
            ->withMaxInputLength(self::PANJANG_HTML_MAKS * 4);

        // Elemen lain dibuang beserta isinya; pembungkus umum hasil tempel (Word, web) cukup
        // dilepas tagnya agar teksnya tidak ikut hilang.
        foreach (self::PEMBUNGKUS as $elemen) {
            $config = $config->blockElement($elemen);
        }

        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * HTML aman untuk disimpan. Isi tanpa teks terlihat (mis. editor kosong "<p></p>")
     * menjadi string kosong agar aturan required tetap bekerja.
     */
    public function bersihkan(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $bersih = trim($this->sanitizer->sanitize($html));

        if (TeksHtml::polos($bersih) === '') {
            return '';
        }

        // Paragraf kosong di akhir (sisa baris baru di editor) tidak perlu disimpan.
        return (string) preg_replace('#(?:<p(?:\s[^>]*)?>(?:\s|&nbsp;|<br\s*/?>)*</p>)+$#u', '', $bersih);
    }
}
