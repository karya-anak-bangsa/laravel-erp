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
        $bulan = array_map(fn (array $baris) => CarbonImmutable::createFromFormat('!Y-m', $baris['bulan']), $perBulan);

        return view('admin.dashboard', [
            'hariIni' => $hariIni,
            'saldoTotal' => $saldoKas->saldoTotal(),
            'jumlahAkunAktif' => AkunKas::query()->where('status_aktif', true)->count(),
            'bulanIni' => $laporanKas->ringkasan($hariIni->startOfMonth(), $hariIni),
            // Float cukup untuk menggambar grafik; angka presisi tetap ada di laporan.
            'grafik' => [
                // Sumbu X: "Nov" saja; tahun hanya di bulan pertama & Januari agar 12 label muat.
                'label' => array_map(
                    fn (?CarbonImmutable $tanggal, int $i) => $tanggal?->translatedFormat($i === 0 || $tanggal->month === 1 ? "M\nY" : 'M'),
                    $bulan,
                    array_keys($bulan),
                ),
                'labelPanjang' => array_map(fn (?CarbonImmutable $tanggal) => $tanggal?->translatedFormat('F Y'), $bulan),
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
