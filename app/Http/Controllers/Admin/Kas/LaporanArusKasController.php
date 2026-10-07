<?php

namespace App\Http\Controllers\Admin\Kas;

use App\Http\Controllers\Controller;
use App\Models\Kas\AkunKas;
use App\Services\Kas\LaporanKasService;
use App\Support\FilterTanggal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanArusKasController extends Controller
{
    public function index(Request $request, LaporanKasService $laporanKas): View
    {
        // Default: awal bulan s.d. hari ini, karena laporan paling sering dibuka untuk bulan berjalan.
        $sampai = FilterTanggal::parse($request->string('sampai')->value()) ?? today()->toImmutable();
        $dari = FilterTanggal::parse($request->string('dari')->value()) ?? $sampai->startOfMonth();

        if ($dari->greaterThan($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $akun = $request->integer('akun') ?: null;

        return view('admin.kas.laporan-arus-kas.index', [
            'dari' => $dari,
            'sampai' => $sampai,
            'akun' => $akun,
            'ringkasan' => $laporanKas->ringkasan($dari, $sampai, $akun),
            'rincian' => $laporanKas->rincianPerKategori($dari, $sampai, $akun),
            'opsiAkun' => AkunKas::query()->orderBy('nama_akun')->pluck('nama_akun', 'id_akun_kas'),
        ]);
    }
}
