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
        // Default satu bulan kalender penuh (1 – akhir bulan berjalan). Bila hanya satu ujung
        // diisi, ujung lainnya mengikuti bulan tanggal tersebut.
        $dari = FilterTanggal::parse($request->string('dari')->value());
        $sampai = FilterTanggal::parse($request->string('sampai')->value());

        $dari ??= ($sampai ?? today()->toImmutable())->startOfMonth();
        $sampai ??= $dari->endOfMonth()->startOfDay();

        if ($dari->greaterThan($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $akun = $request->integer('akun') ?: null;

        return view('admin.kas.laporan-arus-kas.index', [
            'dari' => $dari,
            'sampai' => $sampai,
            'akun' => $akun,
            'ringkasan' => $laporanKas->ringkasan($dari, $sampai, $akun),
            'rincian' => $laporanKas->rincianTransaksi($dari, $sampai, $akun),
            'opsiAkun' => AkunKas::query()->orderBy('nama_akun')->pluck('nama_akun', 'id_akun_kas'),
        ]);
    }
}
