<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Konfigurasi Pest
|--------------------------------------------------------------------------
|
| Feature test memakai database MySQL laravel_erp_testing (lihat phpunit.xml)
| dan di-reset setiap test. Unit test yang butuh container Laravel atau
| database (mis. Service) didaftarkan per folder di sini.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // Aset Vite tidak di-build saat test, jadi @vite dirender kosong. withoutVite() tidak memalsukan @fonts,
    // maka manifest font diarahkan ke berkas yang sengaja tidak ada agar @fonts juga kosong dan test tidak
    // bergantung pada isi public/build. Kecocokan alias font diperiksa lewat npm run build.
    ->beforeEach(function () {
        $this->withoutVite();
        app(Vite::class)->useFontsManifestFilename('fonts-manifest.testing.json');
    })
    ->in('Feature');

// Unit test Service butuh container Laravel & database (mis. storage, transaksi DB).
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Unit/Services');
