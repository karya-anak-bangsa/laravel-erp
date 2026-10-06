<?php

use App\Enums\Kas\JenisAkunKas;
use App\Models\Kas\AkunKas;
use App\Models\Pengguna;

function dataAkunKas(array $timpa = []): array
{
    return array_merge([
        'nama_akun' => 'Rekening BCA Operasional',
        'jenis_akun' => 'bank',
        'nama_bank' => 'BCA',
        'nomor_rekening' => '1234567890',
        'saldo_awal' => '1250000',
        'tanggal_saldo_awal' => '2026-10-01',
        'status_aktif' => '1',
        'keterangan' => 'Rekening utama',
    ], $timpa);
}

beforeEach(function () {
    $this->pengguna = Pengguna::factory()->create();
});

describe('akses tamu', function () {
    it('mengarahkan tamu ke halaman login', function (string $metode, string $rute, bool $butuhModel) {
        $parameter = $butuhModel ? [AkunKas::factory()->create()] : [];

        $this->call($metode, route($rute, $parameter))->assertRedirect(route('login'));
    })->with([
        ['GET', 'admin.akun-kas.index', false],
        ['GET', 'admin.akun-kas.create', false],
        ['POST', 'admin.akun-kas.store', false],
        ['GET', 'admin.akun-kas.edit', true],
        ['PUT', 'admin.akun-kas.update', true],
        ['DELETE', 'admin.akun-kas.destroy', true],
    ]);
});

describe('daftar', function () {
    it('menampilkan akun kas beserta format rupiah dan menandai menu aktif', function () {
        AkunKas::factory()->create([
            'nama_akun' => 'Rekening BCA Operasional',
            'saldo_awal' => 1250000,
            'tanggal_saldo_awal' => '2026-10-01',
        ]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index'))
            ->assertOk()
            ->assertSee('Rekening BCA Operasional')
            ->assertSee('Rp 1.250.000')
            ->assertSee('01 Oktober 2026')
            ->assertSee('class="nav-link active" href="'.route('admin.akun-kas.index').'"', false);
    });

    it('mengurutkan akun kas berdasarkan abjad nama akun', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening BNI']);
        AkunKas::factory()->tunai()->create(['nama_akun' => 'Kas Tunai']);
        AkunKas::factory()->create(['nama_akun' => 'GoPay']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index'))
            ->assertSeeInOrder(['GoPay', 'Kas Tunai', 'Rekening BNI']);
    });

    it('menampilkan empty state bila belum ada akun', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index'))
            ->assertSee('Belum ada akun kas')
            ->assertSee('Tambah Akun Pertama');
    });

    it('mencari berdasarkan nama akun, nama bank, atau nomor rekening', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening Operasional', 'nama_bank' => 'Mandiri', 'nomor_rekening' => '111']);
        AkunKas::factory()->tunai()->create(['nama_akun' => 'Kas Kecil Kantor']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index', ['q' => 'mandiri']))
            ->assertSee('Rekening Operasional')
            ->assertDontSee('Kas Kecil Kantor');

        $this->get(route('admin.akun-kas.index', ['q' => 'kecil']))
            ->assertSee('Kas Kecil Kantor')
            ->assertDontSee('Rekening Operasional');
    });

    it('memfilter berdasarkan jenis dan status', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening Aktif']);
        AkunKas::factory()->nonaktif()->create(['nama_akun' => 'Rekening Lama']);
        AkunKas::factory()->tunai()->create(['nama_akun' => 'Kas Kecil Kantor']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index', ['jenis' => 'tunai']))
            ->assertSee('Kas Kecil Kantor')
            ->assertDontSee('Rekening Aktif')
            ->assertDontSee('Rekening Lama');

        $this->get(route('admin.akun-kas.index', ['status' => 'nonaktif']))
            ->assertSee('Rekening Lama')
            ->assertDontSee('Rekening Aktif')
            ->assertDontSee('Kas Kecil Kantor');
    });

    it('menampilkan pesan tidak ditemukan bila pencarian kosong', function () {
        AkunKas::factory()->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index', ['q' => 'tidak-ada']))
            ->assertSee('Akun kas tidak ditemukan')
            ->assertSee('Reset');
    });

    it('membagi daftar menjadi 15 data per halaman', function () {
        AkunKas::factory()->count(16)->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index'))
            ->assertViewHas('akunKas', fn ($paginator) => $paginator->count() === 15 && $paginator->total() === 16)
            ->assertSee('Menampilkan 1–15 dari 16 data');
    });

    it('tidak menampilkan akun yang sudah dihapus', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening Terhapus'])->delete();

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.index'))
            ->assertDontSee('Rekening Terhapus');
    });
});

describe('tambah', function () {
    it('menampilkan form tambah dengan status aktif tercentang', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.create'))
            ->assertOk()
            ->assertSee('Tambah Akun Kas')
            ->assertSee('name="status_aktif" value="1" checked', false)
            ->assertSee('value="'.today()->format('Y-m-d').'"', false);
    });

    it('menyimpan akun kas baru', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.akun-kas.store'), dataAkunKas())
            ->assertRedirect(route('admin.akun-kas.index'))
            ->assertSessionHas('success', 'Akun kas berhasil ditambahkan.');

        $akun = AkunKas::sole();
        expect($akun->nama_akun)->toBe('Rekening BCA Operasional')
            ->and($akun->jenis_akun)->toBe(JenisAkunKas::Bank)
            ->and($akun->saldo_awal)->toBe('1250000.00')
            ->and($akun->tanggal_saldo_awal->format('Y-m-d'))->toBe('2026-10-01')
            ->and($akun->status_aktif)->toBeTrue();
    });

    it('menyimpan status nonaktif saat switch dimatikan', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.akun-kas.store'), dataAkunKas(['status_aktif' => '0']))
            ->assertSessionHasNoErrors();

        expect(AkunKas::sole()->status_aktif)->toBeFalse();
    });

    it('mengosongkan data bank untuk kas tunai', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.akun-kas.store'), dataAkunKas([
                'nama_akun' => 'Kas Tunai',
                'jenis_akun' => 'tunai',
            ]))
            ->assertSessionHasNoErrors();

        $akun = AkunKas::sole();
        expect($akun->nama_bank)->toBeNull()
            ->and($akun->nomor_rekening)->toBeNull();
    });

    it('menolak data tidak valid dengan pesan berbahasa Indonesia', function (array $timpa, string $kolom, string $pesan) {
        $this->actingAs($this->pengguna)
            ->from(route('admin.akun-kas.create'))
            ->post(route('admin.akun-kas.store'), dataAkunKas($timpa))
            ->assertRedirect(route('admin.akun-kas.create'))
            ->assertSessionHasErrors([$kolom => $pesan]);

        expect(AkunKas::count())->toBe(0);
    })->with([
        'nama kosong' => [['nama_akun' => ''], 'nama_akun', 'Kolom nama akun wajib diisi.'],
        'jenis tidak dikenal' => [['jenis_akun' => 'kripto'], 'jenis_akun', 'Jenis akun yang dipilih tidak valid.'],
        'bank tanpa nama bank' => [['nama_bank' => ''], 'nama_bank', 'Nama bank wajib diisi untuk akun bank.'],
        'bank tanpa nomor rekening' => [['nomor_rekening' => ''], 'nomor_rekening', 'Nomor rekening wajib diisi untuk akun bank.'],
        'saldo negatif' => [['saldo_awal' => '-1'], 'saldo_awal', 'Kolom saldo awal harus bernilai minimal 0.'],
        'saldo bukan angka' => [['saldo_awal' => '1.250.000'], 'saldo_awal', 'Kolom saldo awal harus berupa angka.'],
        'saldo tiga desimal' => [['saldo_awal' => '10.555'], 'saldo_awal', 'Kolom saldo awal harus memiliki 0-2 angka desimal.'],
        'tanggal masa depan' => [['tanggal_saldo_awal' => '2999-01-01'], 'tanggal_saldo_awal', 'Tanggal saldo awal tidak boleh melewati hari ini.'],
    ]);

    it('menolak nama akun yang sudah dipakai', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening BCA Operasional']);

        $this->actingAs($this->pengguna)
            ->post(route('admin.akun-kas.store'), dataAkunKas())
            ->assertSessionHasErrors(['nama_akun' => 'Nama akun sudah dipakai akun kas lain.']);
    });

    it('mengizinkan nama akun yang sama dengan akun terhapus', function () {
        AkunKas::factory()->create(['nama_akun' => 'Rekening BCA Operasional'])->delete();

        $this->actingAs($this->pengguna)
            ->post(route('admin.akun-kas.store'), dataAkunKas())
            ->assertSessionHasNoErrors();

        expect(AkunKas::count())->toBe(1);
    });
});

describe('ubah', function () {
    it('menampilkan form ubah berisi data akun', function () {
        $akun = AkunKas::factory()->create(['nama_akun' => 'Rekening Mandiri']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.edit', $akun))
            ->assertOk()
            ->assertSee('Ubah Akun Kas')
            ->assertSee('value="Rekening Mandiri"', false)
            ->assertSee('name="_method" value="PUT"', false);
    });

    it('memperbarui akun kas', function () {
        $akun = AkunKas::factory()->create();

        $this->actingAs($this->pengguna)
            ->put(route('admin.akun-kas.update', $akun), dataAkunKas([
                'nama_akun' => 'Dompet DANA',
                'jenis_akun' => 'e_wallet',
                'nama_bank' => 'DANA',
                'nomor_rekening' => '081234567890',
                'status_aktif' => '0',
            ]))
            ->assertRedirect(route('admin.akun-kas.index'))
            ->assertSessionHas('success', 'Akun kas berhasil diperbarui.');

        $akun->refresh();
        expect($akun->nama_akun)->toBe('Dompet DANA')
            ->and($akun->jenis_akun)->toBe(JenisAkunKas::EWallet)
            ->and($akun->status_aktif)->toBeFalse();
    });

    it('mengizinkan menyimpan ulang dengan nama akun sendiri', function () {
        $akun = AkunKas::factory()->create(['nama_akun' => 'Rekening BCA Operasional']);

        $this->actingAs($this->pengguna)
            ->put(route('admin.akun-kas.update', $akun), dataAkunKas())
            ->assertSessionHasNoErrors();
    });

    it('menolak nama akun milik akun lain', function () {
        AkunKas::factory()->create(['nama_akun' => 'Kas Tunai']);
        $akun = AkunKas::factory()->create();

        $this->actingAs($this->pengguna)
            ->put(route('admin.akun-kas.update', $akun), dataAkunKas(['nama_akun' => 'Kas Tunai']))
            ->assertSessionHasErrors(['nama_akun' => 'Nama akun sudah dipakai akun kas lain.']);
    });

    it('mengembalikan 404 untuk akun yang sudah dihapus', function () {
        $akun = AkunKas::factory()->create();
        $akun->delete();

        $this->actingAs($this->pengguna)
            ->get(route('admin.akun-kas.edit', $akun))
            ->assertNotFound();
    });
});

describe('hapus', function () {
    it('menghapus akun kas secara soft delete', function () {
        $akun = AkunKas::factory()->create();

        $this->actingAs($this->pengguna)
            ->delete(route('admin.akun-kas.destroy', $akun))
            ->assertRedirect(route('admin.akun-kas.index'))
            ->assertSessionHas('success', 'Akun kas berhasil dihapus.');

        $this->assertSoftDeleted($akun);
    });
});
