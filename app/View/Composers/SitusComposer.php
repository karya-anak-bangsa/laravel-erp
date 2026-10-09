<?php

namespace App\View\Composers;

use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\IdentitasService;
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
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $atribut = $this->request->attributes;

        if (! $atribut->has(self::KUNCI)) {
            $atribut->set(self::KUNCI, $this->identitasService->untukSitus());
        }

        $view->with('identitas', $atribut->get(self::KUNCI));

        // Daftar layanan di footer: halaman yang sudah memuat layanan (beranda) tidak perlu query ulang.
        if ($view->name() === 'layouts.web.footer' && ! $view->offsetExists('layanan')) {
            $view->with('layanan', Layanan::query()->berurutan()->get(['id_layanan', 'judul']));
        }
    }
}
