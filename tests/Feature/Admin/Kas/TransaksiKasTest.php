<?php

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function dataTransaksiKas(array $timpa = []): array
{
    return array_merge([
        'tanggal_transaksi' => '2026-09-15',
        'id_akun_kas' => test()->akun->id_akun_kas,
        'id_kategori_transaksi' => test()->kategoriKeluar->id_kategori_transaksi,
        'jumlah' => '1250000',
        'nama_pihak' => 'Hostinger',
        'keterangan' => 'Perpanjangan domain karyaanakbangsa.co.id',
    ], $timpa);
}

beforeEach(function () {
    Storage::fake('local');
    $this->pengguna = Pengguna::factory()->create();
    $this->akun = AkunKas::factory()->create(['nama_akun' => 'Rekening BCA Operasional', 'tanggal_saldo_awal' => '2026-09-01']);
    $this->kategoriKeluar = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
    $this->kategoriMasuk = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
});

describe('akses tamu', function () {
    it('mengarahkan tamu ke halaman login', function (string $metode, string $rute, bool $butuhModel) {
        $parameter = $butuhModel ? [TransaksiKas::factory()->create()] : [];

        $this->call($metode, route($rute, $parameter))->assertRedirect(route('login'));
    })->with([
        ['GET', 'admin.transaksi-kas.index', false],
        ['GET', 'admin.transaksi-kas.create', false],
        ['POST', 'admin.transaksi-kas.store', false],
        ['GET', 'admin.transaksi-kas.show', true],
        ['GET', 'admin.transaksi-kas.edit', true],
        ['PUT', 'admin.transaksi-kas.update', true],
        ['DELETE', 'admin.transaksi-kas.destroy', true],
        ['GET', 'admin.transaksi-kas.bukti', true],
    ]);
});

describe('daftar', function () {
    it('menampilkan transaksi beserta relasinya dan menandai menu aktif', function () {
        TransaksiKas::factory()->create([
            'nomor_transaksi' => 'KK-202609-0001',
            'id_akun_kas' => $this->akun,
            'id_kategori_transaksi' => $this->kategoriKeluar,
            'tanggal_transaksi' => '2026-09-15',
            'jumlah' => 1250000,
            'nama_pihak' => 'Hostinger',
            'keterangan' => 'Perpanjangan domain tahunan',
        ]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertOk()
            ->assertSee('KK-202609-0001')
            ->assertSee('15 September 2026')
            ->assertSee('Domain &amp; Hosting', false)
            ->assertSee('Rekening BCA Operasional')
            ->assertSee('Rp 1.250.000')
            // Kolom keterangan (beserta nama pihak) tidak ditampilkan di daftar, hanya di detail.
            ->assertDontSee('<th>Keterangan</th>', false)
            ->assertDontSee('Perpanjangan domain tahunan')
            ->assertDontSee('Hostinger')
            ->assertSee('<span class="chip chip-red">Pengeluaran</span>', false)
            ->assertSee('class="nav-link active" href="'.route('admin.transaksi-kas.index').'"', false);
    });

    it('mengurutkan dari tanggal transaksi terbaru', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9003', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-20']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9002', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-10']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertSeeInOrder(['KK-202609-9003', 'KK-202609-9002', 'KK-202609-9001']);
    });

    it('menampilkan empty state bila belum ada transaksi', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertSee('Belum ada transaksi kas')
            ->assertSee('Catat Transaksi Pertama');
    });

    it('mencari berdasarkan nomor, nama pihak, atau keterangan', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01', 'nama_pihak' => 'Hostinger', 'keterangan' => 'Domain']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9002', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01', 'nama_pihak' => 'Notaris', 'keterangan' => 'Akta pendirian']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index', ['q' => 'hostinger']))
            ->assertSee('KK-202609-9001')
            ->assertDontSee('KK-202609-9002');

        $this->get(route('admin.transaksi-kas.index', ['q' => 'akta']))
            ->assertSee('KK-202609-9002')
            ->assertDontSee('KK-202609-9001');

        $this->get(route('admin.transaksi-kas.index', ['q' => '9002']))
            ->assertSee('KK-202609-9002')
            ->assertDontSee('KK-202609-9001');
    });

    it('memfilter berdasarkan periode tanggal', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202608-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-08-31']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9002', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-30']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202610-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-10-01']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index', ['dari' => '2026-09-01', 'sampai' => '2026-09-30']))
            ->assertSee('KK-202609-9001')
            ->assertSee('KK-202609-9002')
            ->assertDontSee('KK-202608-9001')
            ->assertDontSee('KK-202610-9001');
    });

    it('mengabaikan format tanggal filter yang tidak valid', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index', ['dari' => 'kemarin', 'sampai' => '2026-13-45']))
            ->assertOk()
            ->assertSee('KK-202609-9001');
    });

    it('memfilter berdasarkan jenis, akun, dan kategori', function () {
        $akunLain = AkunKas::factory()->create(['tanggal_saldo_awal' => '2026-01-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KM-202609-9001', 'id_kategori_transaksi' => $this->kategoriMasuk, 'id_akun_kas' => $this->akun, 'tanggal_transaksi' => '2026-09-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'id_akun_kas' => $akunLain, 'tanggal_transaksi' => '2026-09-01']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index', ['jenis' => 'pemasukan']))
            ->assertSee('KM-202609-9001')
            ->assertDontSee('KK-202609-9001');

        $this->get(route('admin.transaksi-kas.index', ['akun' => $akunLain->id_akun_kas]))
            ->assertSee('KK-202609-9001')
            ->assertDontSee('KM-202609-9001');

        $this->get(route('admin.transaksi-kas.index', ['kategori' => $this->kategoriMasuk->id_kategori_transaksi]))
            ->assertSee('KM-202609-9001')
            ->assertDontSee('KK-202609-9001');
    });

    it('menyusun filter: cari, jenis, akun, periode, lalu tombol terapkan tanpa dropdown kategori', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertSeeInOrder(['name="q"', 'name="jenis"', 'name="akun"', 'name="dari"', 'name="sampai"', 'Terapkan'], false)
            ->assertDontSee('name="kategori"', false);
    });

    it('menampilkan pesan tidak ditemukan bila pencarian kosong', function () {
        TransaksiKas::factory()->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index', ['q' => 'tidak-ada']))
            ->assertSee('Transaksi tidak ditemukan')
            ->assertSee('Reset');
    });

    it('membagi daftar menjadi 25 data per halaman', function () {
        TransaksiKas::factory()->count(26)->create(['id_akun_kas' => $this->akun, 'id_kategori_transaksi' => $this->kategoriKeluar]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertViewHas('transaksiKas', fn ($paginator) => $paginator->count() === 25 && $paginator->total() === 26)
            ->assertSee('Menampilkan 1–25 dari 26 data');
    });

    it('tidak menampilkan transaksi yang sudah dihapus', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01'])->delete();

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.index'))
            ->assertDontSee('KK-202609-9001');
    });
});

describe('catat', function () {
    it('menampilkan form dengan akun aktif saja dan kategori dikelompokkan per jenis', function () {
        AkunKas::factory()->nonaktif()->create(['nama_akun' => 'Rekening Lama']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.create'))
            ->assertOk()
            ->assertSee('Catat Transaksi Kas')
            ->assertSee('Rekening BCA Operasional')
            ->assertDontSee('Rekening Lama')
            ->assertSeeInOrder(['<optgroup label="Pemasukan">', 'Jasa Pembuatan Website', '<optgroup label="Pengeluaran">', 'Domain &amp; Hosting'], false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('data-confirm-title="Simpan transaksi baru?"', false)
            ->assertSee('data-rupiah', false)
            ->assertSee('value="'.today()->format('Y-m-d').'"', false);
    });

    it('menyimpan transaksi dengan nomor otomatis dan jenis dari kategori', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas(['jenis_transaksi' => 'pemasukan']))
            ->assertRedirect(route('admin.transaksi-kas.index'))
            ->assertSessionHas('success', 'Transaksi KK-202609-0001 berhasil dicatat.');

        $transaksi = TransaksiKas::sole();
        expect($transaksi->nomor_transaksi)->toBe('KK-202609-0001')
            ->and($transaksi->jenis_transaksi)->toBe(JenisTransaksi::Pengeluaran)
            ->and($transaksi->jumlah)->toBe('1250000.00')
            ->and($transaksi->created_by)->toBe($this->pengguna->id_pengguna)
            ->and($transaksi->updated_by)->toBe($this->pengguna->id_pengguna);
    });

    it('menyimpan bukti transaksi di disk privat', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas([
                'bukti_transaksi' => UploadedFile::fake()->create('nota.pdf', 200, 'application/pdf'),
            ]))
            ->assertSessionHasNoErrors();

        $path = TransaksiKas::sole()->bukti_transaksi;
        expect($path)->toStartWith('kas/bukti/');
        Storage::disk('local')->assertExists($path);
    });

    it('menolak data tidak valid dengan pesan berbahasa Indonesia', function (array $timpa, string $kolom, string $pesan) {
        $this->actingAs($this->pengguna)
            ->from(route('admin.transaksi-kas.create'))
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas($timpa))
            ->assertRedirect(route('admin.transaksi-kas.create'))
            ->assertSessionHasErrors([$kolom => $pesan]);

        expect(TransaksiKas::count())->toBe(0);
    })->with([
        'tanggal kosong' => [['tanggal_transaksi' => ''], 'tanggal_transaksi', 'Kolom tanggal transaksi wajib diisi.'],
        'tanggal masa depan' => [['tanggal_transaksi' => '2999-01-01'], 'tanggal_transaksi', 'Tanggal transaksi tidak boleh melewati hari ini.'],
        'tanggal sebelum saldo awal' => [['tanggal_transaksi' => '2026-08-31'], 'tanggal_transaksi', 'Tanggal transaksi tidak boleh sebelum tanggal saldo awal akun (01 September 2026).'],
        'akun kosong' => [['id_akun_kas' => ''], 'id_akun_kas', 'Kolom akun kas wajib diisi.'],
        'akun tidak ada' => [['id_akun_kas' => 999999], 'id_akun_kas', 'Akun kas yang dipilih tidak valid atau sudah nonaktif.'],
        'kategori kosong' => [['id_kategori_transaksi' => ''], 'id_kategori_transaksi', 'Kolom kategori transaksi wajib diisi.'],
        'jumlah nol' => [['jumlah' => '0'], 'jumlah', 'Jumlah harus lebih dari 0.'],
        'jumlah negatif' => [['jumlah' => '-5000'], 'jumlah', 'Jumlah harus lebih dari 0.'],
        'jumlah berformat titik' => [['jumlah' => '1.250.000'], 'jumlah', 'Kolom jumlah harus berupa angka.'],
        'keterangan kosong' => [['keterangan' => ''], 'keterangan', 'Kolom keterangan wajib diisi.'],
    ]);

    it('menolak akun kas nonaktif', function () {
        $akunNonaktif = AkunKas::factory()->nonaktif()->create(['tanggal_saldo_awal' => '2026-01-01']);

        $this->actingAs($this->pengguna)
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas(['id_akun_kas' => $akunNonaktif->id_akun_kas]))
            ->assertSessionHasErrors(['id_akun_kas' => 'Akun kas yang dipilih tidak valid atau sudah nonaktif.']);
    });

    it('menolak kategori yang sudah dihapus', function () {
        $this->kategoriKeluar->delete();

        $this->actingAs($this->pengguna)
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas())
            ->assertSessionHasErrors(['id_kategori_transaksi' => 'Kategori transaksi yang dipilih tidak valid.']);
    });

    it('menolak bukti selain gambar atau PDF dan yang melebihi 5 MB', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.transaksi-kas.store'), dataTransaksiKas([
                'bukti_transaksi' => UploadedFile::fake()->create('skrip.exe', 10, 'application/x-msdownload'),
            ]))
            ->assertSessionHasErrors(['bukti_transaksi' => 'Bukti transaksi harus berupa gambar (JPG, PNG, WEBP) atau PDF.']);

        $this->post(route('admin.transaksi-kas.store'), dataTransaksiKas([
            'bukti_transaksi' => UploadedFile::fake()->create('besar.pdf', 5121, 'application/pdf'),
        ]))
            ->assertSessionHasErrors(['bukti_transaksi' => 'Ukuran bukti transaksi maksimal 5 MB.']);

        expect(Storage::disk('local')->allFiles())->toBeEmpty();
    });
});

describe('detail & bukti', function () {
    it('menampilkan rincian transaksi beserta pencatatnya', function () {
        $transaksi = TransaksiKas::factory()->create([
            'nomor_transaksi' => 'KK-202609-0001',
            'id_akun_kas' => $this->akun,
            'id_kategori_transaksi' => $this->kategoriKeluar,
            'tanggal_transaksi' => '2026-09-15',
            'jumlah' => 1250000,
            'keterangan' => 'Perpanjangan domain',
            'created_by' => $this->pengguna->id_pengguna,
        ]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.show', $transaksi))
            ->assertOk()
            ->assertSee('KK-202609-0001')
            ->assertSee('15 September 2026')
            ->assertSee('Rp 1.250.000')
            ->assertSee('Perpanjangan domain')
            ->assertSee($this->pengguna->nama)
            ->assertSee('Belum ada bukti');
    });

    it('menampilkan pratinjau dan mengirim bukti gambar lewat rute ber-auth', function () {
        Storage::disk('local')->put('kas/bukti/nota.png', 'isi-gambar');
        $transaksi = TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-0001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01', 'bukti_transaksi' => 'kas/bukti/nota.png']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.show', $transaksi))
            ->assertSee('<img src="'.route('admin.transaksi-kas.bukti', $transaksi).'"', false);

        $respons = $this->get(route('admin.transaksi-kas.bukti', $transaksi))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        expect($respons->headers->get('Content-Disposition'))->toContain('inline')->toContain('bukti-KK-202609-0001.png')
            ->and($respons->streamedContent())->toBe('isi-gambar');
    });

    it('mengembalikan 404 bila transaksi tidak punya bukti atau berkasnya hilang', function () {
        $tanpaBukti = TransaksiKas::factory()->create();
        $berkasHilang = TransaksiKas::factory()->create(['bukti_transaksi' => 'kas/bukti/hilang.pdf']);

        $this->actingAs($this->pengguna)->get(route('admin.transaksi-kas.bukti', $tanpaBukti))->assertNotFound();
        $this->get(route('admin.transaksi-kas.bukti', $berkasHilang))->assertNotFound();
    });

    it('mengembalikan 404 untuk transaksi yang sudah dihapus', function () {
        $transaksi = TransaksiKas::factory()->create();
        $transaksi->delete();

        $this->actingAs($this->pengguna)->get(route('admin.transaksi-kas.show', $transaksi))->assertNotFound();
    });
});

describe('ubah', function () {
    beforeEach(function () {
        $this->transaksi = TransaksiKas::factory()->create([
            'nomor_transaksi' => 'KK-202609-0001',
            'id_akun_kas' => $this->akun,
            'id_kategori_transaksi' => $this->kategoriKeluar,
            'tanggal_transaksi' => '2026-09-15',
            'created_by' => $this->pengguna->id_pengguna,
        ]);
    });

    it('menampilkan form ubah hanya dengan kategori sejenis', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.edit', $this->transaksi))
            ->assertOk()
            ->assertSee('Ubah Transaksi Kas')
            ->assertSee('Domain &amp; Hosting', false)
            ->assertDontSee('Jasa Pembuatan Website')
            ->assertSee('data-confirm="Perubahan transaksi KK-202609-0001 akan disimpan."', false)
            ->assertSee('name="_method" value="PUT"', false);
    });

    it('tetap menawarkan akun milik transaksi walau kini nonaktif', function () {
        $this->akun->update(['status_aktif' => false]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.edit', $this->transaksi))
            ->assertSee('Rekening BCA Operasional');

        $this->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas())
            ->assertSessionHasNoErrors();
    });

    it('memperbarui transaksi dan mencatat pengubah', function () {
        $pengubah = Pengguna::factory()->create();

        $this->actingAs($pengubah)
            ->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas(['jumlah' => '750000', 'nama_pihak' => '']))
            ->assertRedirect(route('admin.transaksi-kas.index'))
            ->assertSessionHas('success', 'Transaksi KK-202609-0001 berhasil diperbarui.');

        $this->transaksi->refresh();
        expect($this->transaksi->jumlah)->toBe('750000.00')
            ->and($this->transaksi->nama_pihak)->toBeNull()
            ->and($this->transaksi->created_by)->toBe($this->pengguna->id_pengguna)
            ->and($this->transaksi->updated_by)->toBe($pengubah->id_pengguna);
    });

    it('menolak kategori dengan jenis berbeda', function () {
        $this->actingAs($this->pengguna)
            ->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas(['id_kategori_transaksi' => $this->kategoriMasuk->id_kategori_transaksi]))
            ->assertSessionHasErrors(['id_kategori_transaksi' => 'Kategori harus berjenis pengeluaran seperti transaksi semula.']);
    });

    it('menolak pindah ke akun nonaktif lain', function () {
        $akunNonaktif = AkunKas::factory()->nonaktif()->create(['tanggal_saldo_awal' => '2026-01-01']);

        $this->actingAs($this->pengguna)
            ->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas(['id_akun_kas' => $akunNonaktif->id_akun_kas]))
            ->assertSessionHasErrors(['id_akun_kas' => 'Akun kas yang dipilih tidak valid atau sudah nonaktif.']);
    });

    it('mengganti bukti lama dengan unggahan baru', function () {
        Storage::disk('local')->put('kas/bukti/lama.pdf', 'lama');
        $this->transaksi->update(['bukti_transaksi' => 'kas/bukti/lama.pdf']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.transaksi-kas.edit', $this->transaksi))
            ->assertSee('Lihat file saat ini')
            ->assertSee('Hapus bukti saat ini');

        $this->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas([
            'bukti_transaksi' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasNoErrors();

        Storage::disk('local')->assertMissing('kas/bukti/lama.pdf');
        Storage::disk('local')->assertExists($this->transaksi->refresh()->bukti_transaksi);
    });

    it('menghapus bukti saat switch hapus bukti dinyalakan', function () {
        Storage::disk('local')->put('kas/bukti/lama.pdf', 'lama');
        $this->transaksi->update(['bukti_transaksi' => 'kas/bukti/lama.pdf']);

        $this->actingAs($this->pengguna)
            ->put(route('admin.transaksi-kas.update', $this->transaksi), dataTransaksiKas(['hapus_bukti' => '1']))
            ->assertSessionHasNoErrors();

        expect($this->transaksi->refresh()->bukti_transaksi)->toBeNull();
        Storage::disk('local')->assertMissing('kas/bukti/lama.pdf');
    });
});

describe('hapus', function () {
    it('menghapus transaksi secara soft delete dan mencatat penghapusnya', function () {
        $transaksi = TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-0001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);

        $this->actingAs($this->pengguna)
            ->delete(route('admin.transaksi-kas.destroy', $transaksi))
            ->assertRedirect(route('admin.transaksi-kas.index'))
            ->assertSessionHas('success', 'Transaksi KK-202609-0001 berhasil dihapus.');

        $this->assertSoftDeleted($transaksi);
        expect(TransaksiKas::withTrashed()->find($transaksi->id_transaksi_kas)->updated_by)->toBe($this->pengguna->id_pengguna);
    });
});
