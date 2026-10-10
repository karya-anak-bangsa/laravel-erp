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
     * Nomor dari tautan WhatsApp dalam format lokal yang mudah dibaca, mis. 'https://wa.me/6281234567890'
     * (atau api.whatsapp.com/send?phone=…) → '0812-3456-7890'. Bila nomor tidak terbaca, tautan tanpa skema.
     */
    public static function tampilanWhatsapp(string $url): string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $kueri);
        $sumber = is_string($kueri['phone'] ?? null) ? $kueri['phone'] : (string) parse_url($url, PHP_URL_PATH);
        $angka = (string) preg_replace('/\D+/', '', $sumber);

        if (strlen($angka) < 8) {
            return rtrim(Str::after($url, '://'), '/');
        }

        $lokal = str_starts_with($angka, '62') ? '0'.substr($angka, 2) : $angka;

        return implode('-', array_filter([substr($lokal, 0, 4), substr($lokal, 4, 4), substr($lokal, 8)], fn (string $bagian) => $bagian !== ''));
    }

    /**
     * Email yang boleh patah baris setelah '@' di kartu sempit; isi tetap di-escape.
     */
    public static function emailBisaPatah(string $email): HtmlString
    {
        return new HtmlString(str_replace('@', '@<wbr>', e($email)));
    }
}
