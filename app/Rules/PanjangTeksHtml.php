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
    public function __construct(private readonly int $maks) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (mb_strlen(TeksHtml::polos($value)) > $this->maks) {
            $fail("Kolom :attribute tidak boleh lebih dari {$this->maks} karakter.");

            return;
        }

        if (mb_strlen($value) > HtmlSanitizerService::PANJANG_HTML_MAKS) {
            $fail('Format kolom :attribute terlalu banyak; kurangi tautan atau format teks.');
        }
    }
}
