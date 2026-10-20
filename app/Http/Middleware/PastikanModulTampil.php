<?php

namespace App\Http\Middleware;

use App\Enums\CompanyProfile\JenisModul;
use App\Services\CompanyProfile\ModulWebsiteService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Halaman publik milik modul yang dinonaktifkan di Manajemen Modul dianggap tidak ada (404).
 * Pakai: Route::middleware('modul:artikel').
 */
class PastikanModulTampil
{
    public function __construct(private readonly ModulWebsiteService $modulWebsite) {}

    public function handle(Request $request, Closure $next, string $jenis): Response
    {
        abort_unless($this->modulWebsite->tampil(JenisModul::from($jenis)), 404);

        return $next($request);
    }
}
