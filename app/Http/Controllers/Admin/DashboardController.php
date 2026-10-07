<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kas\AkunKas;
use App\Models\Kas\TransaksiKas;
use App\Services\Kas\LaporanKasService;
use App\Services\Kas\SaldoKasService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(SaldoKasService $saldoKas, LaporanKasService $laporanKas): View
    {
        $hariIni = today()->toImmutable();
        $perBulan = $laporanKas->perBulan($hariIni);

        return view('admin.dashboard', [
            'hariIni' => $hariIni,
            'saldoTotal' => $saldoKas->saldoTotal(),
            'jumlahAkunAktif' => AkunKas::query()->where('status_aktif', true)->count(),
            'bulanIni' => $laporanKas->ringkasan($hariIni->startOfMonth(), $hariIni),
            // Float cukup untuk menggambar grafik; angka presisi tetap ada di laporan.
            'grafik' => [
                'label' => array_map(fn (array $bulan) => CarbonImmutable::createFromFormat('!Y-m', $bulan['bulan'])?->translatedFormat('M Y'), $perBulan),
                'pemasukan' => array_map(fn (array $bulan) => (float) $bulan['pemasukan'], $perBulan),
                'pengeluaran' => array_map(fn (array $bulan) => (float) $bulan['pengeluaran'], $perBulan),
            ],
            'transaksiTerbaru' => TransaksiKas::query()
                ->with(['akunKas', 'kategoriTransaksi'])
                ->latest('tanggal_transaksi')
                ->latest('id_transaksi_kas')
                ->limit(6)
                ->get(),
        ]);
    }
}
