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
        // Satu tahun kalender (Jan–Des) agar sumbu grafik tetap sama sepanjang tahun;
        // bulan yang belum tiba bernilai 0.
        $perBulan = $laporanKas->perBulan($hariIni->endOfYear());
        $bulan = array_map(fn (array $baris) => CarbonImmutable::createFromFormat('!Y-m', $baris['bulan']), $perBulan);

        return view('admin.dashboard', [
            'hariIni' => $hariIni,
            'saldoTotal' => $saldoKas->saldoTotal(),
            'jumlahAkunAktif' => AkunKas::query()->where('status_aktif', true)->count(),
            'bulanIni' => $laporanKas->ringkasan($hariIni->startOfMonth(), $hariIni),
            // Float cukup untuk menggambar grafik; angka presisi tetap ada di laporan.
            'grafik' => [
                // Tahun sudah ada di judul kartu, jadi sumbu X cukup nama bulan singkat.
                'label' => array_map(fn (?CarbonImmutable $tanggal) => $tanggal?->translatedFormat('M'), $bulan),
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
