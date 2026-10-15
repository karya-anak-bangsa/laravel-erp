<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Alamat & gambar untuk mesin pencari dan pratinjau tautan (kanonis, Open Graph, sitemap, JSON-LD).
 */
class Seo
{
    // Gambar yang dibagikan berasal dari unggahan Company Profile (hero, artikel), semuanya di disk public.
    private const DISK_GAMBAR = 'public';

    /**
     * Alamat mutlak berdasar APP_URL, bukan host yang sedang dibuka pengunjung (mis. www. atau domain sementara
     * hosting), agar mesin pencari hanya mengenal satu alamat per halaman. Alamat yang sudah mutlak dibiarkan.
     * Pakai: Seo::alamat(route('artikel.index', [], false)) → https://karyaanakbangsa.co.id/artikel
     */
    public static function alamat(string $alamat = '/'): string
    {
        if (preg_match('#^https?://#i', $alamat) === 1) {
            return $alamat;
        }

        $akar = rtrim((string) config('app.url'), '/');
        $path = '/'.ltrim($alamat, '/');

        return $path === '/' ? $akar : $akar.$path;
    }

    /**
     * Lebar & tinggi gambar unggahan untuk og:image:width/height: tanpa keduanya Facebook bisa saja tidak
     * menampilkan gambar pada bagikan pertama. Null bila alamat bukan berkas disk public atau berkasnya
     * tidak terbaca sebagai gambar.
     *
     * @return array{0: int, 1: int}|null
     */
    public static function ukuranGambar(string $alamat): ?array
    {
        $disk = Storage::disk(self::DISK_GAMBAR);
        // Dibandingkan per path agar alamat mutlak maupun relatif (/storage/…) sama-sama dikenali.
        $awalan = (string) parse_url($disk->url(''), PHP_URL_PATH);
        $path = (string) parse_url($alamat, PHP_URL_PATH);

        if ($awalan === '' || ! str_starts_with($path, $awalan)) {
            return null;
        }

        $berkas = rawurldecode(substr($path, strlen($awalan)));

        if ($berkas === '' || ! $disk->exists($berkas)) {
            return null;
        }

        // Berkas rusak memicu notice; cukup dianggap tanpa ukuran, halaman tidak boleh gagal karenanya.
        $ukuran = @getimagesize($disk->path($berkas));

        return $ukuran === false ? null : [$ukuran[0], $ukuran[1]];
    }
}
