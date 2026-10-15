<?php

namespace App\View\Composers;

use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\IdentitasService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Data halaman error (errors::*). View error hanya berisi judul & pesan; layout dipilih dari alamat: panel admin
 * dan login memakai layout error admin, selain itu layout publik bertema. Halaman error publik tidak boleh ikut
 * gagal saat database tidak terbaca, jadi data situsnya dibaca di sini dengan cadangan.
 */
class HalamanErrorComposer
{
    public function __construct(
        private readonly IdentitasService $identitasService,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $exception = $view->getData()['exception'] ?? null;

        $view->with('kodeError', $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500);

        if ($this->request->is('admin', 'admin/*', 'login', 'logout')) {
            $view->with('layoutError', 'layouts.error');

            return;
        }

        $view->with(['layoutError' => 'layouts.web-error', ...$this->dataSitus()]);
    }

    /**
     * Identitas null = tampilan ringkas tanpa navbar & footer (layouts/web-error). Layanan footer ikut dibaca di
     * sini agar kegagalan database ketahuan sebelum footer dirender, bukan di tengahnya.
     *
     * @return array{identitas: Identitas|null, layanan: Collection<int, Layanan>}
     */
    private function dataSitus(): array
    {
        try {
            return ['identitas' => $this->identitasService->untukSitus(), 'layanan' => Layanan::untukTautan()];
        } catch (Throwable $e) {
            Log::warning('Data situs tidak terbaca saat merender halaman error; memakai tampilan ringkas.', ['galat' => $e->getMessage()]);

            return ['identitas' => null, 'layanan' => new Collection];
        }
    }
}
