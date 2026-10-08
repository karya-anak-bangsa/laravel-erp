<?php

namespace App\Rules;

use App\Services\Shared\HtmlSanitizerService;
use App\Support\TeksHtml;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Batas panjang isian editor WYSIWYG dihitung dari teks yang terlihat, bukan markup
 * HTML-nya, ditambah batas total HTML agar muat di kolom TEXT.
 */
class PanjangTeksHtml implements ValidationRule
{
    /**
     * @param  int  $maksHtml  batas total HTML; isi artikel (LONGTEXT) memakai HtmlSanitizerService::PANJANG_HTML_ARTIKEL_MAKS
     */
    public function __construct(
        private readonly int $maks,
        private readonly int $maksHtml = HtmlSanitizerService::PANJANG_HTML_MAKS,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (mb_strlen(TeksHtml::polos($value)) > $this->maks) {
            $fail("Kolom :attribute tidak boleh lebih dari {$this->maks} karakter.");

            return;
        }

        if (mb_strlen($value) > $this->maksHtml) {
            $fail('Format kolom :attribute terlalu banyak; kurangi tautan atau format teks.');
        }
    }
}
