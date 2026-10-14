<?php

namespace App\View\Composers;

use App\Models\CompanyProfile\Template;
use App\Services\CompanyProfile\TemaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tema pilihan pengunjung (cookie) dan token template aktif untuk layout publik. Satu query template per
 * halaman; bila database gagal, TemaService memakai config sehingga halaman error tetap aman.
 */
class TemaComposer
{
    public function __construct(
        private readonly TemaService $temaService,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        // Pratinjau admin (TemplateController::pratinjau) menitipkan template yang belum tentu tersimpan di atribut request.
        $pratinjau = $this->request->attributes->get(TemaService::PRATINJAU);
        $pratinjau = $pratinjau instanceof Template ? $pratinjau : null;
        $this->temaService->pratinjau($pratinjau);

        $view->with([
            'tema' => $this->temaService->jenisDariRequest($this->request),
            'tokenTemplate' => $this->temaService->tokenCss(),
            'fontTema' => $this->temaService->aliasFont(),
            'templatePratinjau' => $pratinjau,
        ]);
    }
}
