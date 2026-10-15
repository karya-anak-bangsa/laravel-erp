<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile\Artikel;
use App\Support\Seo;
use Illuminate\Http\Response;

/**
 * Berkas untuk perayap mesin pencari, berlaku untuk seluruh situs (semua modul). Alamatnya dibentuk dari APP_URL
 * agar selalu menunjuk alamat utama, termasuk saat dibuka lewat www.
 */
class SeoController extends Controller
{
    /**
     * Beranda, daftar artikel, dan semua artikel terbit. Halaman paginasi tidak dimasukkan karena perayap
     * menemukannya lewat tautan di halaman daftar.
     */
    public function sitemap(): Response
    {
        $artikel = Artikel::query()->terbit()
            ->orderByDesc('tanggal')
            ->orderByDesc('id_artikel')
            ->get(['id_artikel', 'slug', 'tanggal', 'updated_at']);

        return response()->view('seo.sitemap', compact('artikel'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Panel admin & login tidak untuk dirayapi. Sitemap hanya disebut bila situs boleh diindeks (config/seo.php).
     */
    public function robots(): Response
    {
        $baris = ['User-agent: *', 'Disallow: /admin', 'Disallow: /login'];

        if (config('seo.indeks')) {
            array_push($baris, '', 'Sitemap: '.Seo::alamat(route('sitemap', [], false)));
        }

        return response(implode("\n", $baris)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
