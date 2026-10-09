<?php

namespace App\Providers;

use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use App\View\Composers\MenuComposer;
use App\View\Composers\SitusComposer;
use App\View\Composers\TemaComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.sidebar', MenuComposer::class);
        View::composer('layouts.web', TemaComposer::class);
        View::composer(['layouts.web', 'layouts.web.*', 'web.*'], SitusComposer::class);

        // Formulir kontak publik: 3 kiriman per menit per IP (percobaan yang gagal validasi ikut dihitung).
        // Dimatikan saat APP_ENV=local seperti pembatas login, agar tidak mengganggu development.
        RateLimiter::for('kontak', fn (Request $request) => $this->app->isLocal()
            ? Limit::none()
            : Limit::perMinute(3)->by($request->ip())->response(fn () => redirect()->to(KontakKamiController::urlFormulir())
                ->withInput($request->except('_token', 'website'))
                ->withErrors(['formulir' => KontakKamiController::PESAN_DIBATASI])));
    }
}
