<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin
|--------------------------------------------------------------------------
|
| Didaftarkan di bootstrap/app.php dengan prefix /admin, middleware
| web + auth, dan nama rute admin. — jadi tidak perlu diulang di sini.
| Rute tiap modul ditaruh di routes/admin/<modul>.php lalu di-require
| di bawah, mis. require __DIR__.'/admin/company-profile.php';
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

require __DIR__.'/admin/company-profile.php';
