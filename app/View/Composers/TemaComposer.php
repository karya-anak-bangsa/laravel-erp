<?php

namespace App\View\Composers;

use App\Services\CompanyProfile\TemaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tema pilihan pengunjung (cookie) dan token template untuk layout publik. Tidak menyentuh database.
 */
class TemaComposer
{
    public function __construct(
        private readonly TemaService $temaService,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'tema' => $this->temaService->jenisDariRequest($this->request),
            'tokenTemplate' => $this->temaService->tokenCss(),
            'fontTema' => $this->temaService->aliasFont(),
        ]);
    }
}
