<?php

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use Database\Seeders\KasDummySeeder;
use Illuminate\Support\Facades\Storage;

// SEMENTARA: dihapus bersama KasDummySeeder setelah Fase 3 selesai.

beforeEach(function () {
    Storage::fake('local');
    Pengguna::factory()->create();
});

it('membuat data dummy kas yang mematuhi aturan bisnis', function () {
    $this->seed(KasDummySeeder::class);

    $akun = AkunKas::where('keterangan', KasDummySeeder::PENANDA)->get();
    $transaksi = TransaksiKas::withTrashed()->with(['akunKas', 'kategoriTransaksi'])->get();
    $tercatat = $transaksi->whereNull('deleted_at');

    expect($akun)->toHaveCount(5)
        ->and($akun->where('status_aktif', false))->toHaveCount(1)
        ->and($tercatat->count())->toBeGreaterThan(200)
        ->and($transaksi->whereNotNull('deleted_at'))->toHaveCount(5)
        ->and($transaksi->pluck('nomor_transaksi')->unique())->toHaveCount($transaksi->count());

    // Aturan TransaksiKasService: jenis ikut kategori, nomor sesuai jenis & bulan, tanggal dalam rentang.
    expect($transaksi->reject(fn (TransaksiKas $t) => $t->jenis_transaksi === $t->kategoriTransaksi->jenis_transaksi))->toBeEmpty()
        ->and($transaksi->reject(fn (TransaksiKas $t) => str_starts_with(
            $t->nomor_transaksi,
            $t->jenis_transaksi->awalanNomor().'-'.$t->tanggal_transaksi->format('Ym').'-',
        )))->toBeEmpty()
        ->and($transaksi->reject(fn (TransaksiKas $t) => $t->tanggal_transaksi->between($t->akunKas->tanggal_saldo_awal, today())))->toBeEmpty();

    // Saldo setiap akun tidak boleh minus.
    foreach ($akun as $a) {
        $milikAkun = $tercatat->where('id_akun_kas', $a->id_akun_kas);
        $saldo = (float) $a->saldo_awal
            + $milikAkun->where('jenis_transaksi', JenisTransaksi::Pemasukan)->sum('jumlah')
            - $milikAkun->where('jenis_transaksi', JenisTransaksi::Pengeluaran)->sum('jumlah');

        expect($saldo)->toBeGreaterThanOrEqual(0);
    }

    // Bukti dummy tersimpan di disk privat dengan awalan yang mudah dibersihkan.
    $bukti = $transaksi->whereNotNull('bukti_transaksi')->pluck('bukti_transaksi');
    expect($bukti)->not->toBeEmpty()
        ->and($bukti->reject(fn (string $path) => str_starts_with($path, 'kas/bukti/'.KasDummySeeder::AWALAN_BUKTI)))->toBeEmpty()
        ->and($bukti->reject(fn (string $path) => Storage::disk('local')->exists($path)))->toBeEmpty();

    // Ada kategori yang tidak dipakai agar tombol hapus kategori bisa dicoba.
    expect($transaksi->pluck('kategoriTransaksi.nama_kategori'))->not->toContain('Pengeluaran Lain-lain');

    // Dijalankan ulang tidak menggandakan data.
    $this->seed(KasDummySeeder::class);

    expect(AkunKas::count())->toBe(5)
        ->and(TransaksiKas::withTrashed()->count())->toBe($transaksi->count());
});

it('menolak dijalankan di produksi', function () {
    app()->detectEnvironment(fn () => 'production');

    $seeder = app(KasDummySeeder::class)->setContainer(app());

    expect(fn () => $seeder())->toThrow(RuntimeException::class, 'tidak boleh dijalankan di produksi');
    expect(AkunKas::count())->toBe(0);
});
