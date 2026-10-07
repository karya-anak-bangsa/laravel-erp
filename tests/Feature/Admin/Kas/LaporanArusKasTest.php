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

it('menampilkan satu bulan kalender penuh secara default dan menandai menu aktif', function () {
    $this->travelTo('2026-10-07 10:00:00');

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index'))
        ->assertOk()
        ->assertSee('Laporan Arus Kas')
        ->assertSee('value="2026-10-01"', false)
        ->assertSee('value="2026-10-31"', false)
        ->assertSee('Periode 01 Oktober 2026 – 31 Oktober 2026')
        ->assertSeeInOrder(['Total Saldo', 'per 31 Oktober 2026'])
        ->assertSee('class="nav-link active" href="'.route('admin.laporan-arus-kas.index').'"', false);
});

it('melengkapi ujung periode yang kosong mengikuti bulan tanggal yang diisi', function () {
    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-02-10']))
        ->assertSee('Periode 10 Februari 2026 – 28 Februari 2026');

    $this->get(route('admin.laporan-arus-kas.index', ['sampai' => '2026-09-15']))
        ->assertSee('Periode 01 September 2026 – 15 September 2026');
});

it('menampilkan ringkasan dan rincian per transaksi periode yang dipilih', function () {
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-09-05', 'jumlah' => 2_000_000]);
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06', 'jumlah' => 500_000]);
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-10-02', 'jumlah' => 111_000]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertOk()
        ->assertSeeInOrder(['Pemasukan', 'Rp 2.000.000', 'Pengeluaran', 'Rp 500.000', 'Total Saldo', 'Rp 2.500.000'])
        ->assertDontSee('Saldo Awal')
        ->assertDontSee('Saldo Akhir')
        ->assertSee('<strong class="nilai-selisih nominal-positif">+Rp 1.500.000</strong>', false)
        ->assertSee('<td colspan="2">Total</td>', false)
        ->assertSee('2 transaksi')
        ->assertSeeInOrder(['Rincian Pemasukan', '05 September 2026', 'Jasa Pembuatan Website', 'Rp 2.000.000'])
        ->assertSeeInOrder(['Rincian Pengeluaran', '06 September 2026', 'Domain &amp; Hosting', 'Rp 500.000'], false)
        ->assertDontSee('Porsi')
        ->assertDontSee('Rp 111.000');
});

it('menampilkan rincian sebagai informasi tanpa tautan ke transaksi', function () {
    $transaksi = TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06']);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('<td class="cell-strong">Domain &amp; Hosting</td>', false)
        ->assertDontSee(route('admin.transaksi-kas.show', $transaksi))
        ->assertDontSee(route('admin.transaksi-kas.index').'?', false);
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
        ->assertSee('Periode 01 Oktober 2026 – 31 Oktober 2026');
});

it('menghitung total saldo per akhir periode, termasuk akun yang dibuka di dalam periode', function () {
    AkunKas::factory()->create(['saldo_awal' => 250_000, 'tanggal_saldo_awal' => '2026-09-20']);
    AkunKas::factory()->create(['saldo_awal' => 900_000, 'tanggal_saldo_awal' => '2026-10-01']);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSeeInOrder(['Total Saldo', 'Rp 1.250.000', 'per 30 September 2026']);
});

it('menampilkan total saldo minus dengan warna merah', function () {
    TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-09-06', 'jumlah' => 1_500_000]);

    $this->actingAs($this->pengguna)
        ->get(route('admin.laporan-arus-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
        ->assertSee('<span class="stat-value nominal-negatif">-Rp 500.000</span>', false)
        ->assertSee('<strong class="nilai-selisih nominal-negatif">-Rp 1.500.000</strong>', false);
});
