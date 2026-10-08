<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Atribut style paragraf & sub-judul hanya boleh berisi perataan teks dari toolbar editor; CSS lain
 * (warna, posisi, url(), dsb. dari teks tempelan) membuang seluruh atribut style.
 * Rata kiri adalah bawaan, jadi tidak disimpan.
 */
class PerataanTeksSanitizer implements AttributeSanitizerInterface
{
    public const PERATAAN = ['center', 'right', 'justify'];

    public function getSupportedElements(): ?array
    {
        return ['p', 'h2', 'h3'];
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $pola = '/^\s*text-align\s*:\s*('.implode('|', self::PERATAAN).')\s*;?\s*$/i';

        if (preg_match($pola, $value, $cocok) !== 1) {
            return null;
        }

        return 'text-align: '.strtolower($cocok[1]);
    }
}
