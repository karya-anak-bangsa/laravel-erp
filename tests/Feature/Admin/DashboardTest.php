<?php

use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;

describe('widget kas', function () {
    beforeEach(function () {
        $this->travelTo('2026-10-07 10:00:00');
        $this->pengguna = Pengguna::factory()->create();
        $this->akun = AkunKas::factory()->create(['nama_akun' => 'Rekening BCA', 'saldo_awal' => 1_000_000, 'tanggal_saldo_awal' => '2026-01-01']);
        $this->jasaWeb = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
        $this->hosting = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
    });

    it('menampilkan saldo total serta pemasukan, pengeluaran, dan jumlah transaksi bulan ini', function () {
        AkunKas::factory()->nonaktif()->create(['saldo_awal' => 500_000, 'tanggal_saldo_awal' => '2026-01-01']);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-09-30', 'jumlah' => 4_000_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-10-02', 'jumlah' => 2_000_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-10-05', 'jumlah' => 300_000]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.dashboard'))
            ->assertOk()
            // 1.000.000 + 500.000 + 4.000.000 + 2.000.000 − 300.000
            ->assertSeeInOrder(['Total Saldo', 'Rp 7.200.000', '1 akun kas aktif'])
            ->assertSeeInOrder(['Pemasukan', 'Rp 2.000.000', 'Oktober 2026'])
            ->assertSeeInOrder(['Pengeluaran', 'Rp 300.000', 'Oktober 2026'])
            ->assertSeeInOrder(['Transaksi', '2', 'Oktober 2026']);
    });

    it('mengirim data grafik Januari sampai Desember tahun berjalan ke halaman', function () {
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-10-02', 'jumlah' => 2_000_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2025-12-15', 'jumlah' => 999_000]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.dashboard'))
            ->assertSee('Arus Kas Tahun 2026')
            ->assertSee('data-grafik-kas=', false)
            ->assertViewHas('grafik', fn (array $grafik) => $grafik['label'] === ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des']
                && $grafik['labelPanjang'][0] === 'Januari 2026'
                && $grafik['labelPanjang'][11] === 'Desember 2026'
                && $grafik['pemasukan'][9] === 2_000_000.0
                && $grafik['pengeluaran'][9] === 0.0
                // Desember 2025 di luar tahun berjalan; Nov–Des 2026 belum tiba.
                && ! in_array(999_000.0, $grafik['pemasukan'], true)
                && $grafik['pemasukan'][11] === 0.0);
    });

    it('menampilkan transaksi terbaru dengan tanda pemasukan dan pengeluaran', function () {
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-10-01', 'jumlah' => 300_000]);
        TransaksiKas::factory()->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->jasaWeb, 'tanggal_transaksi' => '2026-10-05', 'jumlah' => 2_000_000]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder(['Transaksi Terbaru', 'Jasa Pembuatan Website', 'Domain &amp; Hosting'], false)
            ->assertSee('<span class="cell-mono nominal-positif">+Rp 2.000.000</span>', false)
            ->assertSee('<span class="cell-mono nominal-negatif">-Rp 300.000</span>', false);
    });

    it('membatasi transaksi terbaru menjadi 6 data', function () {
        TransaksiKas::factory()->count(8)->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->hosting, 'tanggal_transaksi' => '2026-10-01']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.dashboard'))
            ->assertViewHas('transaksiTerbaru', fn ($transaksi) => $transaksi->count() === 6);
    });

    it('menampilkan empty state bila belum ada transaksi', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.dashboard'))
            ->assertSee('Belum ada transaksi')
            ->assertSee(route('admin.transaksi-kas.create'));
    });
});

it('mengarahkan tamu ke halaman login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('menampilkan dashboard untuk pengguna yang login', function () {
    $pengguna = Pengguna::factory()->create(['nama' => 'Aryajaya Alamsyah']);

    $this->actingAs($pengguna)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Selamat datang, Aryajaya Alamsyah')
        ->assertSee(route('logout'));
});

it('menampilkan menu pengguna berisi logout di sidebar, bukan di topbar', function () {
    $pengguna = Pengguna::factory()->create(['nama' => 'Aryajaya Alamsyah', 'email' => 'admin@karyaanakbangsa.co.id']);

    $this->actingAs($pengguna)
        ->get(route('admin.dashboard'))
        ->assertSee('class="sidebar-user" type="button" data-menu="menu-pengguna"', false)
        ->assertDontSee('tb-user', false)
        ->assertDontSee('topbar-right', false)
        ->assertSee('<template id="menu-pengguna">', false)
        ->assertSee('admin@karyaanakbangsa.co.id')
        ->assertSee('<form method="POST" action="'.route('logout').'">', false)
        ->assertDontSee('theme-toggle', false);
});

it('tidak menyediakan mode gelap', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertDontSee('data-aksi="ganti-tema"', false)
        ->assertDontSee('data-theme', false);
});

it('memakai ikon rumah untuk menu dashboard', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('fa-house', false);
});

it('menandai menu dashboard sebagai menu aktif', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertSee('class="nav-link active"', false);
});
