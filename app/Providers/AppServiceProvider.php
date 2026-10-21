<?php

namespace App\Providers;

use App\Enums\Sistem\PeranPengguna;
use App\Models\Pengguna;
use App\View\Composers\HalamanErrorComposer;
use App\View\Composers\SeoComposer;
use App\View\Composers\SitusComposer;
use App\View\Composers\TemaComposer;
use Illuminate\Support\Facades\Gate;
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
        View::composer(['layouts.web', 'layouts.web-error'], TemaComposer::class);
        View::composer(['layouts.web', 'layouts.web.*', 'web.*'], SitusComposer::class);
        View::composer('layouts.web.seo', SeoComposer::class);
        View::composer('errors::*', HalamanErrorComposer::class);

        // Pengaturan Sistem (identitas, modul, template, pengguna) hanya untuk Super Administrator.
        Gate::define('kelola-sistem', fn (Pengguna $pengguna) => $pengguna->peran === PeranPengguna::SuperAdministrator);
    }
}
