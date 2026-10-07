<?php

namespace App\Services\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\TransaksiKas;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SaldoKasService
{
    /**
     * Menambahkan kolom `saldo` (string desimal) ke setiap akun pada query.
     *
     * Saldo tidak disimpan di tabel; dihitung saldo_awal + Σ pemasukan − Σ pengeluaran.
     * Perhitungan dilakukan di MySQL lewat subquery agar tetap DECIMAL (tanpa pembulatan
     * float PHP) dan tetap satu query walau daftar akun dipaginasi. Transaksi terhapus
     * otomatis dikecualikan oleh scope SoftDeletes.
     *
     * $per = saldo pada akhir tanggal itu; akun yang saldo awalnya tercatat sesudah
     * tanggal tersebut dianggap belum ada (saldo 0).
     *
     * @param  Builder<AkunKas>  $query
     * @return Builder<AkunKas>
     */
    public function denganSaldo(Builder $query, ?CarbonInterface $per = null): Builder
    {
        $mutasi = TransaksiKas::query()
            ->selectRaw(
                'COALESCE(SUM(CASE jenis_transaksi WHEN ? THEN jumlah WHEN ? THEN -jumlah ELSE 0 END), 0)',
                [JenisTransaksi::Pemasukan->value, JenisTransaksi::Pengeluaran->value],
            )
            ->whereColumn('tb_transaksi_kas.id_akun_kas', 'tb_akun_kas.id_akun_kas')
            ->when($per, fn (Builder $q) => $q->where('tanggal_transaksi', '<=', $per->toDateString()))
            ->toBase();

        $saldoAwal = $per
            ? 'CASE WHEN tb_akun_kas.tanggal_saldo_awal <= ? THEN tb_akun_kas.saldo_awal ELSE 0 END'
            : 'tb_akun_kas.saldo_awal';
        $bindings = $per ? [$per->toDateString(), ...$mutasi->getBindings()] : $mutasi->getBindings();

        if ($query->getQuery()->columns === null) {
            $query->select('tb_akun_kas.*');
        }

        return $query->selectRaw("{$saldoAwal} + ({$mutasi->toSql()}) as saldo", $bindings);
    }

    public function saldo(AkunKas $akun, ?CarbonInterface $per = null): string
    {
        return (string) $this->denganSaldo(AkunKas::withTrashed(), $per)
            ->whereKey($akun->getKey())
            ->value('saldo');
    }

    /**
     * Jumlah saldo seluruh akun (aktif & nonaktif, kecuali yang dihapus), atau satu akun saja.
     */
    public function saldoTotal(?CarbonInterface $per = null, ?int $idAkun = null): string
    {
        $saldoPerAkun = $this->denganSaldo(
            AkunKas::query()->when($idAkun, fn (Builder $q) => $q->whereKey($idAkun)),
            $per,
        )->toBase();

        return (string) DB::query()
            ->fromSub($saldoPerAkun, 'saldo_akun')
            ->selectRaw('COALESCE(SUM(saldo), 0) as total')
            ->value('total');
    }
}
