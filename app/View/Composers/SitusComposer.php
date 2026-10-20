<?php

namespace App\View\Composers;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\IdentitasService;
use App\Services\CompanyProfile\ModulWebsiteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data situs untuk view publik. Partial beranda dirender sebelum layout sehingga composer ini dipanggil
 * berkali-kali per halaman; identitas disimpan di atribut request agar cache hanya dibaca sekali.
 */
class SitusComposer
{
    private const KUNCI = 'situs.identitas';

    public function __construct(
        private readonly IdentitasService $identitasService,
        private readonly ModulWebsiteService $modulWebsite,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        // Halaman error publik sudah membawa identitasnya sendiri (HalamanErrorComposer), termasuk null saat
        // database tidak terbaca; jangan dibaca ulang karena justru akan gagal lagi.
        if (! $view->offsetExists('identitas')) {
            $atribut = $this->request->attributes;

            if (! $atribut->has(self::KUNCI)) {
                $atribut->set(self::KUNCI, $this->identitasService->untukSitus());
            }

            $view->with('identitas', $atribut->get(self::KUNCI));
        }

        // Manajemen Modul untuk beranda dan partial yang bergantung padanya: $modul->tampil(JenisModul::…).
        $view->with('modul', $this->modulWebsite);

        if (in_array($view->name(), ['layouts.web.navbar', 'layouts.web.footer'], true)) {
            $view->with('menuUtama', $this->menuUtama());
        }

        // Daftar layanan di footer: halaman yang sudah memuat layanan (beranda) tidak perlu query ulang.
        if ($view->name() === 'layouts.web.footer' && ! $view->offsetExists('layanan')) {
            $view->with('layanan', Layanan::untukTautan());
        }
    }

    /**
     * Menu navbar dan Tautan Cepat footer: modul aktif seurut Manajemen Modul. Seksi beranda lewat alamat beranda +
     * anchor agar tetap berfungsi dari halaman publik lain; Portofolio, Tutorial, dan Artikel punya halaman sendiri.
     *
     * @return array<string, string> label => alamat
     */
    private function menuUtama(): array
    {
        $beranda = route('beranda');
        $menu = ['Beranda' => $beranda.'#beranda'];

        foreach ($this->modulWebsite->urutanTampil() as $jenis) {
            if ($jenis->punyaMenu()) {
                $menu[$jenis->label()] = match ($jenis) {
                    JenisModul::Portofolio => route('portofolio.index'),
                    JenisModul::Tutorial => route('tutorial.index'),
                    JenisModul::Artikel => route('artikel.index'),
                    default => $beranda.'#'.$jenis->value,
                };
            }
        }

        return $menu;
    }
}
