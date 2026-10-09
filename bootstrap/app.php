<?php

use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['web', 'auth'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        // Cookie tema ditulis JavaScript frontend (teks polos); nilainya divalidasi JenisTemplate::dariCookie().
        $middleware->encryptCookies(except: ['tema']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Sesi habis saat mengirim formulir kontak (tab beranda terbuka lama): kembali ke formulir dengan
        // isian utuh, bukan halaman 419 yang membuat pesan yang sudah diketik hilang.
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $e->getPrevious() instanceof TokenMismatchException || ! $request->routeIs('kontak-kami.store')) {
                return null;
            }

            return redirect()->to(KontakKamiController::urlFormulir())
                ->withInput($request->except('_token', 'website'))
                ->withErrors(['formulir' => KontakKamiController::PESAN_SESI_HABIS]);
        });
    })->create();
