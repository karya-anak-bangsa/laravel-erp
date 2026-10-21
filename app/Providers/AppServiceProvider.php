<?php

namespace App\Providers;

use App\Enums\Sistem\AksiAktivitas;
use App\Enums\Sistem\PeranPengguna;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Template;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;
use App\Services\Sistem\LogAktivitasService;
use App\View\Composers\HalamanErrorComposer;
use App\View\Composers\SeoComposer;
use App\View\Composers\SitusComposer;
use App\View\Composers\TemaComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
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

        // Pengaturan Sistem (identitas, modul, template, pengguna, log aktivitas) hanya untuk Super Administrator.
        Gate::define('kelola-sistem', fn (Pengguna $pengguna) => $pengguna->peran === PeranPengguna::SuperAdministrator);

        // Alias jenis data di tb_log_aktivitas (subjek_type) = nama tabel tanpa tb_, agar log tetap terbaca walau class
        // berganti nama. Setiap model ber-trait MencatatAktivitas wajib terdaftar di sini.
        Relation::enforceMorphMap([
            'pengguna' => Pengguna::class,
            'identitas' => Identitas::class,
            'hero' => Hero::class,
            'layanan' => Layanan::class,
            'kategori_portofolio' => KategoriPortofolio::class,
            'portofolio' => Portofolio::class,
            'kategori_tutorial' => KategoriTutorial::class,
            'tutorial' => Tutorial::class,
            'materi_tutorial' => MateriTutorial::class,
            'kategori_artikel' => KategoriArtikel::class,
            'artikel' => Artikel::class,
            'faq' => Faq::class,
            'template' => Template::class,
            'modul_website' => ModulWebsite::class,
        ]);

        // Masuk & keluar panel admin tercatat di Log Aktivitas; perubahan data dicatat trait MencatatAktivitas.
        Event::listen(function (Login $event): void {
            if ($event->user instanceof Pengguna) {
                app(LogAktivitasService::class)->catat(AksiAktivitas::Masuk, $event->user);
            }
        });
        Event::listen(function (Logout $event): void {
            if ($event->user instanceof Pengguna) {
                app(LogAktivitasService::class)->catat(AksiAktivitas::Keluar, $event->user);
            }
        });
    }
}
