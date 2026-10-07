<?php

namespace App\Services\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\TransaksiKas;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// Semua penjumlahan uang dilakukan di MySQL agar hasilnya tetap DECIMAL, bukan float PHP.
class LaporanKasService
{
    public function __construct(private readonly SaldoKasService $saldoKas) {}

    /**
     * Ringkasan arus kas periode. total_saldo = saldo seluruh akun (atau satu akun) per
     * akhir periode; sengaja satu angka saja agar tidak rancu dengan saldo awal akun.
     *
     * @return array{pemasukan: string, pengeluaran: string, selisih: string, total_saldo: string, jumlah_transaksi: int}
     */
    public function ringkasan(CarbonInterface $dari, CarbonInterface $sampai, ?int $idAkun = null): array
    {
        $mutasi = $this->transaksiPeriode($dari, $sampai, $idAkun)
            ->toBase()
            ->selectRaw('COALESCE(SUM(CASE WHEN jenis_transaksi = ? THEN jumlah ELSE 0 END), 0) as pemasukan', [JenisTransaksi::Pemasukan->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN jenis_transaksi = ? THEN jumlah ELSE 0 END), 0) as pengeluaran', [JenisTransaksi::Pengeluaran->value])
            ->selectRaw(
                'COALESCE(SUM(CASE jenis_transaksi WHEN ? THEN jumlah WHEN ? THEN -jumlah ELSE 0 END), 0) as selisih',
                [JenisTransaksi::Pemasukan->value, JenisTransaksi::Pengeluaran->value],
            )
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->first();

        return [
            'pemasukan' => (string) $mutasi->pemasukan,
            'pengeluaran' => (string) $mutasi->pengeluaran,
            'selisih' => (string) $mutasi->selisih,
            'total_saldo' => $this->saldoKas->saldoTotal($sampai, $idAkun),
            'jumlah_transaksi' => (int) $mutasi->jumlah_transaksi,
        ];
    }

    /**
     * Daftar transaksi periode per jenis, urut tanggal lalu nomor transaksi.
     * Kunci = nilai JenisTransaksi. Kategori yang sudah dihapus tetap terbaca
     * karena relasi kategoriTransaksi memakai withTrashed().
     *
     * @return array<string, Collection<int, TransaksiKas>>
     */
    public function rincianTransaksi(CarbonInterface $dari, CarbonInterface $sampai, ?int $idAkun = null): array
    {
        $transaksi = $this->transaksiPeriode($dari, $sampai, $idAkun)
            ->with('kategoriTransaksi:id_kategori_transaksi,nama_kategori')
            ->orderBy('tanggal_transaksi')
            ->orderBy('nomor_transaksi')
            ->get(['id_transaksi_kas', 'nomor_transaksi', 'id_kategori_transaksi', 'jenis_transaksi', 'tanggal_transaksi', 'jumlah']);

        return collect(JenisTransaksi::cases())
            ->mapWithKeys(fn (JenisTransaksi $jenis) => [
                $jenis->value => $transaksi->where('jenis_transaksi', $jenis)->values(),
            ])
            ->all();
    }

    /**
     * Pemasukan & pengeluaran per bulan untuk grafik, berakhir di bulan $bulanTerakhir.
     * Bulan tanpa transaksi tetap muncul dengan nilai 0 agar sumbu waktu tidak bolong.
     *
     * @return list<array{bulan: string, pemasukan: string, pengeluaran: string}>
     */
    public function perBulan(CarbonInterface $bulanTerakhir, int $jumlahBulan = 12, ?int $idAkun = null): array
    {
        // toImmutable: Carbon mutable milik pemanggil tidak ikut berubah.
        $akhir = $bulanTerakhir->toImmutable()->endOfMonth();
        $awal = $akhir->subMonthsNoOverflow($jumlahBulan - 1)->startOfMonth();

        $total = $this->transaksiPeriode($awal, $akhir, $idAkun)
            ->toBase()
            ->selectRaw("DATE_FORMAT(tanggal_transaksi, '%Y-%m') as bulan")
            ->selectRaw('COALESCE(SUM(CASE WHEN jenis_transaksi = ? THEN jumlah ELSE 0 END), 0) as pemasukan', [JenisTransaksi::Pemasukan->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN jenis_transaksi = ? THEN jumlah ELSE 0 END), 0) as pengeluaran', [JenisTransaksi::Pengeluaran->value])
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $hasil = [];
        for ($bulan = $awal; $bulan->lessThanOrEqualTo($akhir); $bulan = $bulan->addMonthNoOverflow()) {
            $kunci = $bulan->format('Y-m');
            $hasil[] = [
                'bulan' => $kunci,
                'pemasukan' => (string) ($total[$kunci]->pemasukan ?? '0.00'),
                'pengeluaran' => (string) ($total[$kunci]->pengeluaran ?? '0.00'),
            ];
        }

        return $hasil;
    }

    /**
     * @return Builder<TransaksiKas>
     */
    private function transaksiPeriode(CarbonInterface $dari, CarbonInterface $sampai, ?int $idAkun): Builder
    {
        return TransaksiKas::query()
            ->whereBetween('tb_transaksi_kas.tanggal_transaksi', [$dari->toDateString(), $sampai->toDateString()])
            ->when($idAkun, fn (Builder $q) => $q->where('tb_transaksi_kas.id_akun_kas', $idAkun));
    }
}
