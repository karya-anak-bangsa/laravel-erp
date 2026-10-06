<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    ->in('Feature');
