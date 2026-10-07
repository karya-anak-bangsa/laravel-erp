<?php

namespace App\Services\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\TransaksiKas;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use stdClass;

// Semua penjumlahan uang dilakukan di MySQL agar hasilnya tetap DECIMAL, bukan float PHP.
class LaporanKasService
{
    public function __construct(private readonly SaldoKasService $saldoKas) {}

    /**
     * Ringkasan arus kas periode beserta mutasi saldo:
     * saldo_awal + saldo_akun_baru + pemasukan − pengeluaran = saldo_akhir.
     *
     * saldo_akun_baru = saldo awal akun yang dibuka di dalam periode; dipisahkan agar
     * tidak tercampur dengan pemasukan, tetapi tetap membuat mutasi saldo seimbang.
     *
     * @return array{saldo_awal: string, saldo_akun_baru: string, pemasukan: string, pengeluaran: string, selisih: string, saldo_akhir: string, jumlah_transaksi: int}
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

        $saldoAkunBaru = AkunKas::query()
            ->when($idAkun, fn (Builder $q) => $q->whereKey($idAkun))
            ->whereBetween('tanggal_saldo_awal', [$dari->toDateString(), $sampai->toDateString()])
            ->toBase()
            ->selectRaw('COALESCE(SUM(saldo_awal), 0) as total')
            ->value('total');

        return [
            'saldo_awal' => $this->saldoKas->saldoTotal($dari->toImmutable()->subDay(), $idAkun),
            'saldo_akun_baru' => (string) $saldoAkunBaru,
            'pemasukan' => (string) $mutasi->pemasukan,
            'pengeluaran' => (string) $mutasi->pengeluaran,
            'selisih' => (string) $mutasi->selisih,
            'saldo_akhir' => $this->saldoKas->saldoTotal($sampai, $idAkun),
            'jumlah_transaksi' => (int) $mutasi->jumlah_transaksi,
        ];
    }

    /**
     * Total per kategori, dikelompokkan per jenis dan diurutkan dari nominal terbesar.
     * Kategori yang sudah dihapus tetap tampil karena transaksinya masih dihitung.
     * Kunci = nilai JenisTransaksi; setiap baris berisi id_kategori_transaksi,
     * nama_kategori, jumlah_transaksi, dan total (string desimal).
     *
     * @return array<string, Collection<int, stdClass>>
     */
    public function rincianPerKategori(CarbonInterface $dari, CarbonInterface $sampai, ?int $idAkun = null): array
    {
        $baris = $this->transaksiPeriode($dari, $sampai, $idAkun)
            ->join('tb_kategori_transaksi', 'tb_kategori_transaksi.id_kategori_transaksi', '=', 'tb_transaksi_kas.id_kategori_transaksi')
            ->toBase()
            ->select([
                'tb_transaksi_kas.jenis_transaksi',
                'tb_transaksi_kas.id_kategori_transaksi',
                'tb_kategori_transaksi.nama_kategori',
            ])
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->selectRaw('SUM(tb_transaksi_kas.jumlah) as total')
            ->groupBy('tb_transaksi_kas.jenis_transaksi', 'tb_transaksi_kas.id_kategori_transaksi', 'tb_kategori_transaksi.nama_kategori')
            ->orderByDesc('total')
            ->orderBy('tb_kategori_transaksi.nama_kategori')
            ->get();

        return collect(JenisTransaksi::cases())
            ->mapWithKeys(fn (JenisTransaksi $jenis) => [
                $jenis->value => $baris->where('jenis_transaksi', $jenis->value)->values(),
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
