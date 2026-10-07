<?php

use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Services\Kas\SaldoKasService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->layanan = app(SaldoKasService::class);
    $this->akun = AkunKas::factory()->create(['saldo_awal' => 1_000_000, 'tanggal_saldo_awal' => '2026-01-01']);
    $this->kategoriMasuk = KategoriTransaksi::factory()->pemasukan()->create();
    $this->kategoriKeluar = KategoriTransaksi::factory()->create();
});

function catatTransaksi(AkunKas $akun, KategoriTransaksi $kategori, string|int $jumlah): TransaksiKas
{
    return TransaksiKas::factory()->create([
        'id_akun_kas' => $akun,
        'id_kategori_transaksi' => $kategori,
        'jumlah' => $jumlah,
    ]);
}

it('sama dengan saldo awal bila belum ada transaksi', function () {
    expect($this->layanan->saldo($this->akun))->toBe('1000000.00');
});

it('menambah pemasukan dan mengurangi pengeluaran dari saldo awal', function () {
    catatTransaksi($this->akun, $this->kategoriMasuk, 500_000);
    catatTransaksi($this->akun, $this->kategoriMasuk, 250_000);
    catatTransaksi($this->akun, $this->kategoriKeluar, 200_000);

    expect($this->layanan->saldo($this->akun))->toBe('1550000.00');
});

it('mengabaikan transaksi yang sudah dihapus', function () {
    catatTransaksi($this->akun, $this->kategoriMasuk, 500_000);
    catatTransaksi($this->akun, $this->kategoriKeluar, 300_000)->delete();

    expect($this->layanan->saldo($this->akun))->toBe('1500000.00');
});

it('hanya menghitung transaksi milik akun itu sendiri', function () {
    $akunLain = AkunKas::factory()->create(['saldo_awal' => 0, 'tanggal_saldo_awal' => '2026-01-01']);
    catatTransaksi($akunLain, $this->kategoriMasuk, 900_000);
    catatTransaksi($this->akun, $this->kategoriKeluar, 100_000);

    expect($this->layanan->saldo($this->akun))->toBe('900000.00')
        ->and($this->layanan->saldo($akunLain))->toBe('900000.00');
});

it('bisa bernilai minus bila pengeluaran melebihi saldo', function () {
    catatTransaksi($this->akun, $this->kategoriKeluar, 1_250_000);

    expect($this->layanan->saldo($this->akun))->toBe('-250000.00');
});

it('menjaga presisi sen tanpa pembulatan float', function () {
    $akun = AkunKas::factory()->create(['saldo_awal' => '9999999999999.10', 'tanggal_saldo_awal' => '2026-01-01']);
    catatTransaksi($akun, $this->kategoriMasuk, '0.20');
    catatTransaksi($akun, $this->kategoriKeluar, '0.01');

    // 9.999.999.999.999,29 tidak bisa direpresentasikan tepat oleh float.
    expect($this->layanan->saldo($akun))->toBe('9999999999999.29');
});

describe('saldo per tanggal', function () {
    it('hanya menghitung transaksi sampai akhir tanggal yang diminta', function () {
        TransaksiKas::factory()->pemasukan()->create(['id_akun_kas' => $this->akun, 'tanggal_transaksi' => '2026-03-10', 'jumlah' => 400_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'tanggal_transaksi' => '2026-03-31', 'jumlah' => 100_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'tanggal_transaksi' => '2026-04-01', 'jumlah' => 50_000]);

        expect($this->layanan->saldo($this->akun, Carbon::parse('2026-03-09')))->toBe('1000000.00')
            ->and($this->layanan->saldo($this->akun, Carbon::parse('2026-03-31')))->toBe('1300000.00')
            ->and($this->layanan->saldo($this->akun))->toBe('1250000.00');
    });

    it('bernilai 0 sebelum tanggal saldo awal akun', function () {
        expect($this->layanan->saldo($this->akun, Carbon::parse('2025-12-31')))->toBe('0.00')
            ->and($this->layanan->saldo($this->akun, Carbon::parse('2026-01-01')))->toBe('1000000.00');
    });
});

describe('saldo total', function () {
    it('menjumlahkan saldo semua akun kecuali akun yang dihapus', function () {
        $akunLain = AkunKas::factory()->nonaktif()->create(['saldo_awal' => 200_000, 'tanggal_saldo_awal' => '2026-01-01']);
        AkunKas::factory()->create(['saldo_awal' => 999_000, 'tanggal_saldo_awal' => '2026-01-01'])->delete();
        catatTransaksi($akunLain, $this->kategoriKeluar, 50_000);

        expect($this->layanan->saldoTotal())->toBe('1150000.00')
            ->and($this->layanan->saldoTotal(idAkun: $akunLain->id_akun_kas))->toBe('150000.00');
    });

    it('bernilai 0 bila belum ada akun', function () {
        $this->akun->delete();

        expect($this->layanan->saldoTotal())->toBe('0.00');
    });

    it('menghitung total per tanggal', function () {
        AkunKas::factory()->create(['saldo_awal' => 300_000, 'tanggal_saldo_awal' => '2026-06-01']);

        expect($this->layanan->saldoTotal(Carbon::parse('2026-05-31')))->toBe('1000000.00')
            ->and($this->layanan->saldoTotal(Carbon::parse('2026-06-01')))->toBe('1300000.00');
    });
});

it('menyertakan saldo setiap akun pada daftar dalam satu query', function () {
    $akunLain = AkunKas::factory()->create(['saldo_awal' => 50_000, 'tanggal_saldo_awal' => '2026-01-01']);
    catatTransaksi($this->akun, $this->kategoriMasuk, 500_000);
    catatTransaksi($akunLain, $this->kategoriKeluar, 20_000);

    DB::enableQueryLog();
    $saldo = $this->layanan->denganSaldo(AkunKas::query())->get()->pluck('saldo', 'id_akun_kas');

    expect(DB::getQueryLog())->toHaveCount(1)
        ->and($saldo->all())->toBe([
            $this->akun->id_akun_kas => '1500000.00',
            $akunLain->id_akun_kas => '30000.00',
        ]);
});
