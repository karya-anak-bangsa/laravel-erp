<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Corak warna kartu portofolio per kategori (kelas .corak-* di resources/css/web/token.css, hanya
 * berwarna di tema Full Color). Kategori teks bebas, jadi kategori baru tetap mendapat corak yang stabil.
 */
class AksenWarna
{
    public const CORAK = ['sky', 'indigo', 'teal', 'violet', 'rose', 'amber'];

    // Selaras dengan warna ilustrasi layanan dari seeder.
    private const PETA = [
        'website' => 'sky',
        'mobile-apps' => 'indigo',
        'pelatihan-it' => 'teal',
        'sertifikasi-it' => 'violet',
        'bootcamp' => 'rose',
    ];

    /**
     * Nama kelas corak, mis. 'corak-sky'.
     */
    public static function untuk(string $kategori): string
    {
        $slug = Str::slug($kategori);

        return 'corak-'.(self::PETA[$slug] ?? self::CORAK[crc32($slug) % count(self::CORAK)]);
    }
}
