<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanyProfile\RingkasanService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RingkasanService $ringkasan): View
    {
        return view('admin.dashboard', [
            'statistik' => $ringkasan->statistik(),
            'terakhirDiperbarui' => $ringkasan->terakhirDiperbarui(),
        ]);
    }
}
