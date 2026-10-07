<?php

use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Services\Kas\LaporanKasService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->layanan = app(LaporanKasService::class);
    $this->akun = AkunKas::factory()->create(['saldo_awal' => 1_000_000, 'tanggal_saldo_awal' => '2026-01-01']);
    $this->jasaWeb = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
    $this->pelatihan = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Pelatihan IT']);
    $this->hosting = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
});

function transaksiLaporan(AkunKas $akun, KategoriTransaksi $kategori, string $tanggal, int|string $jumlah): TransaksiKas
{
    return TransaksiKas::factory()->create([
        'id_akun_kas' => $akun,
        'id_kategori_transaksi' => $kategori,
        'tanggal_transaksi' => $tanggal,
        'jumlah' => $jumlah,
    ]);
}

function tanggalUji(string $nilai): Carbon
{
    return Carbon::parse($nilai);
}

describe('ringkasan', function () {
    it('menghitung pemasukan, pengeluaran, dan selisih hanya di dalam periode', function () {
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-08-31', 999_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-01', 2_000_000);
        transaksiLaporan($this->akun, $this->pelatihan, '2026-09-30', 500_000);
        transaksiLaporan($this->akun, $this->hosting, '2026-09-15', 300_000);
        transaksiLaporan($this->akun, $this->hosting, '2026-10-01', 888_000);

        $ringkasan = $this->layanan->ringkasan(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        expect($ringkasan)->toMatchArray([
            'pemasukan' => '2500000.00',
            'pengeluaran' => '300000.00',
            'selisih' => '2200000.00',
            'jumlah_transaksi' => 3,
        ]);
    });

    it('menghitung total saldo per akhir periode tanpa transaksi sesudahnya', function () {
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-08-10', 400_000);
        transaksiLaporan($this->akun, $this->hosting, '2026-09-10', 700_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-10-05', 100_000);

        $ringkasan = $this->layanan->ringkasan(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        // 1.000.000 + 400.000 − 700.000; pemasukan 5 Oktober belum dihitung.
        expect($ringkasan['selisih'])->toBe('-700000.00')
            ->and($ringkasan['total_saldo'])->toBe('700000.00');
    });

    it('menyertakan saldo awal akun yang dibuka di dalam periode ke total saldo', function () {
        $akunBaru = AkunKas::factory()->create(['saldo_awal' => 250_000, 'tanggal_saldo_awal' => '2026-09-20']);
        transaksiLaporan($akunBaru, $this->jasaWeb, '2026-09-25', 50_000);

        $ringkasan = $this->layanan->ringkasan(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        // 1.000.000 + 250.000 (akun baru) + 50.000 (pemasukan) = 1.300.000
        expect($ringkasan['pemasukan'])->toBe('50000.00')
            ->and($ringkasan['total_saldo'])->toBe('1300000.00');
    });

    it('bisa dibatasi pada satu akun', function () {
        $akunLain = AkunKas::factory()->create(['saldo_awal' => 5_000_000, 'tanggal_saldo_awal' => '2026-01-01']);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-10', 100_000);
        transaksiLaporan($akunLain, $this->jasaWeb, '2026-09-10', 900_000);

        $ringkasan = $this->layanan->ringkasan(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'), $this->akun->id_akun_kas);

        expect($ringkasan['pemasukan'])->toBe('100000.00')
            ->and($ringkasan['total_saldo'])->toBe('1100000.00');
    });

    it('mengabaikan transaksi yang sudah dihapus', function () {
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-10', 100_000)->delete();

        $ringkasan = $this->layanan->ringkasan(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        expect($ringkasan['pemasukan'])->toBe('0.00')
            ->and($ringkasan['jumlah_transaksi'])->toBe(0);
    });

    it('tidak mengubah tanggal milik pemanggil', function () {
        $dari = tanggalUji('2026-09-01');

        $this->layanan->ringkasan($dari, tanggalUji('2026-09-30'));

        expect($dari->toDateString())->toBe('2026-09-01');
    });
});

describe('rincian transaksi', function () {
    it('memisahkan per jenis dan mengurutkan menurut tanggal di dalam periode', function () {
        transaksiLaporan($this->akun, $this->pelatihan, '2026-09-20', 300_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-03', 1_000_000);
        transaksiLaporan($this->akun, $this->pelatihan, '2026-09-10', 200_000);
        transaksiLaporan($this->akun, $this->hosting, '2026-09-05', 150_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-10-01', 999_000);

        $rincian = $this->layanan->rincianTransaksi(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        expect($rincian['pemasukan']->map(fn ($t) => [$t->tanggal_transaksi->toDateString(), $t->kategoriTransaksi->nama_kategori, $t->jumlah])->all())
            ->toBe([
                ['2026-09-03', 'Jasa Pembuatan Website', '1000000.00'],
                ['2026-09-10', 'Pelatihan IT', '200000.00'],
                ['2026-09-20', 'Pelatihan IT', '300000.00'],
            ])
            ->and($rincian['pengeluaran'])->toHaveCount(1)
            ->and($rincian['pengeluaran']->first()->kategoriTransaksi->nama_kategori)->toBe('Domain & Hosting');
    });

    it('bisa dibatasi pada satu akun dan mengabaikan transaksi yang dihapus', function () {
        $akunLain = AkunKas::factory()->create(['tanggal_saldo_awal' => '2026-01-01']);
        transaksiLaporan($akunLain, $this->jasaWeb, '2026-09-10', 900_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-11', 100_000)->delete();
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-09-12', 50_000);

        $rincian = $this->layanan->rincianTransaksi(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'), $this->akun->id_akun_kas);

        expect($rincian['pemasukan']->pluck('jumlah')->all())->toBe(['50000.00']);
    });

    it('tetap menampilkan kategori yang sudah dihapus', function () {
        transaksiLaporan($this->akun, $this->hosting, '2026-09-05', 150_000);
        $this->hosting->delete();

        $rincian = $this->layanan->rincianTransaksi(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        expect($rincian['pengeluaran']->first()->kategoriTransaksi->nama_kategori)->toBe('Domain & Hosting');
    });

    it('mengembalikan daftar kosong untuk jenis tanpa transaksi', function () {
        $rincian = $this->layanan->rincianTransaksi(tanggalUji('2026-09-01'), tanggalUji('2026-09-30'));

        expect($rincian['pemasukan'])->toBeEmpty()
            ->and($rincian['pengeluaran'])->toBeEmpty();
    });
});

describe('per bulan', function () {
    it('mengembalikan 12 bulan berurutan termasuk bulan tanpa transaksi', function () {
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-02-10', 100_000);
        transaksiLaporan($this->akun, $this->jasaWeb, '2026-02-20', 50_000);
        transaksiLaporan($this->akun, $this->hosting, '2026-10-01', 75_000);
        transaksiLaporan($this->akun, $this->hosting, '2025-10-31', 10_000);

        $data = $this->layanan->perBulan(tanggalUji('2026-10-07'));

        expect($data)->toHaveCount(12)
            ->and($data[0])->toBe(['bulan' => '2025-11', 'pemasukan' => '0.00', 'pengeluaran' => '0.00'])
            ->and($data[3])->toBe(['bulan' => '2026-02', 'pemasukan' => '150000.00', 'pengeluaran' => '0.00'])
            ->and($data[11])->toBe(['bulan' => '2026-10', 'pemasukan' => '0.00', 'pengeluaran' => '75000.00']);
    });

    it('tidak melompati bulan pendek saat mundur dari tanggal 31', function () {
        $bulan = array_column($this->layanan->perBulan(tanggalUji('2026-03-31'), 3), 'bulan');

        expect($bulan)->toBe(['2026-01', '2026-02', '2026-03']);
    });
});
