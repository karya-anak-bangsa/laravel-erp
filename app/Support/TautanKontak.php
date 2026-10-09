<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Format data kontak identitas untuk frontend, dipisah dari Blade agar bisa diuji.
 */
class TautanKontak
{
    /**
     * Tautan tel: berformat internasional, mis. '0812-3456-7890' → 'tel:+6281234567890'.
     */
    public static function telepon(string $nomor): string
    {
        $angka = (string) preg_replace('/\D+/', '', $nomor);

        if (str_starts_with(trim($nomor), '+') || str_starts_with($angka, '62')) {
            return 'tel:+'.$angka;
        }

        return str_starts_with($angka, '0') ? 'tel:+62'.substr($angka, 1) : 'tel:'.$angka;
    }

    /**
     * Teks tautan WhatsApp tanpa skema, mis. 'https://wa.me/62812…' → 'wa.me/62812…'.
     */
    public static function tampilanWhatsapp(string $url): string
    {
        return rtrim(Str::after($url, '://'), '/');
    }

    /**
     * Email yang boleh patah baris setelah '@' di kartu sempit; isi tetap di-escape.
     */
    public static function emailBisaPatah(string $email): HtmlString
    {
        return new HtmlString(str_replace('@', '@<wbr>', e($email)));
    }
}
