<?php

use App\Http\Controllers\Admin\CompanyProfile\HeroController;
use App\Http\Controllers\Admin\CompanyProfile\IdentitasController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin — Company Profile
|--------------------------------------------------------------------------
*/

// Singleton: identitas selalu satu baris, jadi tanpa index/create/destroy.
Route::singleton('identitas', IdentitasController::class)->only(['edit', 'update']);

Route::resource('hero', HeroController::class)->except('show');
