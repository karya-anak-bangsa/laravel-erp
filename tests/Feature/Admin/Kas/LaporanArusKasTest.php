<?php

use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;

beforeEach(function () {
    $this->pengguna = Pengguna::factory()->create();
    $this->akun = AkunKas::factory()->create(['nama_akun' => 'Rekening BCA', 'saldo_awal' => 1_000_000, 'tanggal_saldo_awal' => '2026-01-01']);
    $this->jasaWeb = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
    $this->hosting = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
});

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.laporan-arus-kas.index'))->assertRedirect(route('login'));
});

it('menampilkan periode bulan berjalan secara default dan menandai menu aktif', function () {
    $this->travelTo('2026-10-07 10:00:00');

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index'))
        ->assertOk()
        ->assertSee('Laporan Arus Kas')
        ->assertSee('value="2026-10-01"', false)
        ->assertSee('value="2026-10-07"', false)
        ->assertSee('Periode 01 Oktober 2026 – 07 Oktober 2026')
        ->assertSee('per 30 September 2026')
        ->assertSee('class="nav-link active" href="'.route('admin.laporan-arus-kas.index').'"', false);
});

it('menampilkan ringkasan dan rincian per kategori periode yang dipilih', function () {
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-09-05', 'jumlah' => 2_000_000]);
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06', 'jumlah' => 500_000]);
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-10-02', 'jumlah' => 111_000]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertOk()
        ->assertSeeInOrder(['Saldo Awal', 'Rp 1.000.000', 'Pemasukan', 'Rp 2.000.000', 'Pengeluaran', 'Rp 500.000', 'Saldo Akhir', 'Rp 2.500.000'])
        ->assertSee('+Rp 1.500.000')
        ->assertSee('2 transaksi')
        ->assertSee('Jasa Pembuatan Website')
        ->assertSee('Domain &amp; Hosting', false)
        ->assertDontSee('Rp 111.000');
});

it('menautkan kategori ke daftar transaksi dengan filter yang sama', function () {
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06']);

    $tautan = route('admin.transaksi-kas.index', [
        'kategori' => $this->hosting->id_kategori_transaksi,
        'akun' => $this->akun->id_akun_kas,
        'dari' => '2026-09-01',
        'sampai' => '2026-09-30',
    ]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['akun' => $this->akun->id_akun_kas, 'dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('href="'.e($tautan).'"', false);
});

it('memfilter berdasarkan akun', function () {
    $akunLain = AkunKas::factory()->create(['nama_akun' => 'Kas Tunai', 'saldo_awal' => 0, 'tanggal_saldo_awal' => '2026-01-01']);
    TransaksiKas::factory()->create(['id_akun_kas' => $akunLain, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-09-05', 'jumlah' => 777_000]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['akun' => $this->akun->id_akun_kas, 'dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('<option value="'.$this->akun->id_akun_kas.'" selected>Rekening BCA</option>', false)
        ->assertSeeInOrder(['Periode', '· Rekening BCA', '· 0 transaksi'])
        ->assertSee('Tidak ada pemasukan')
        ->assertDontSee('Rp 777.000');
});

it('menukar tanggal bila dari melewati sampai dan mengabaikan tanggal tidak valid', function () {
    $this->travelTo('2026-10-07 10:00:00');

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-30', 'sampai' => '2026-09-01']))
        ->assertSee('Periode 01 September 2026 – 30 September 2026');

    $this->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-02-31', 'sampai' => 'besok']))
        ->assertOk()
        ->assertSee('Periode 01 Oktober 2026 – 07 Oktober 2026');
});

it('menjelaskan saldo awal akun yang dibuka di dalam periode', function () {
    AkunKas::factory()->create(['saldo_awal' => 250_000, 'tanggal_saldo_awal' => '2026-09-20']);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('Saldo akhir sudah termasuk saldo awal Rp 250.000');
});

it('menampilkan saldo akhir minus dengan warna merah', function () {
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06', 'jumlah' => 1_500_000]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('<span class="stat-value nominal-negatif">-Rp 500.000</span>', false)
        ->assertSee('<span class="nominal-negatif">-Rp 1.500.000</span>', false);
});
