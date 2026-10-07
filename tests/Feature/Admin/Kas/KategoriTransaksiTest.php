<?php

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;

function dataKategoriTransaksi(array $timpa = []): array
{
    return array_merge([
        'nama_kategori' => 'Domain & Hosting',
        'jenis_transaksi' => 'pengeluaran',
        'keterangan' => 'Domain, hosting, dan SSL',
    ], $timpa);
}

beforeEach(function () {
    $this->pengguna = Pengguna::factory()->create();
});

describe('akses tamu', function () {
    it('mengarahkan tamu ke halaman login', function (string $metode, string $rute, bool $butuhModel) {
        $parameter = $butuhModel ? [KategoriTransaksi::factory()->create()] : [];

        $this->call($metode, route($rute, $parameter))->assertRedirect(route('login'));
    })->with([
        ['GET', 'admin.kategori-transaksi.index', false],
        ['GET', 'admin.kategori-transaksi.create', false],
        ['POST', 'admin.kategori-transaksi.store', false],
        ['GET', 'admin.kategori-transaksi.edit', true],
        ['PUT', 'admin.kategori-transaksi.update', true],
        ['DELETE', 'admin.kategori-transaksi.destroy', true],
    ]);
});

describe('daftar', function () {
    it('menampilkan kategori beserta jenisnya dan menandai menu aktif', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index'))
            ->assertOk()
            ->assertSee('Domain &amp; Hosting', false)
            ->assertSee('<span class="chip chip-red">Pengeluaran</span>', false)
            ->assertSee('class="nav-link active" href="'.route('admin.kategori-transaksi.index').'"', false);
    });

    it('mengelompokkan pemasukan lebih dulu lalu mengurutkan abjad nama', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Pajak']);
        KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Pelatihan IT']);
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
        KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Bootcamp']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index'))
            ->assertSeeInOrder(['Bootcamp', 'Pelatihan IT', 'Domain &amp; Hosting', 'Pajak'], false);
    });

    it('menampilkan empty state bila belum ada kategori', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index'))
            ->assertSee('Belum ada kategori transaksi')
            ->assertSee('Tambah Kategori Pertama');
    });

    it('mencari berdasarkan nama kategori atau keterangan', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting', 'keterangan' => 'Termasuk SSL']);
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Transportasi']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index', ['q' => 'ssl']))
            ->assertSee('Domain &amp; Hosting', false)
            ->assertDontSee('Transportasi');

        $this->get(route('admin.kategori-transaksi.index', ['q' => 'transport']))
            ->assertSee('Transportasi')
            ->assertDontSee('Domain &amp; Hosting', false);
    });

    it('memfilter berdasarkan jenis transaksi', function () {
        KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Pemasaran']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index', ['jenis' => 'pemasukan']))
            ->assertSee('Jasa Pembuatan Website')
            ->assertDontSee('Pemasaran');
    });

    it('membawa filter jenis ke tombol tambah', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index', ['jenis' => 'pemasukan']))
            ->assertSee('href="'.route('admin.kategori-transaksi.create', ['jenis' => 'pemasukan']).'"', false);
    });

    it('menampilkan pesan tidak ditemukan bila pencarian kosong', function () {
        KategoriTransaksi::factory()->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index', ['q' => 'tidak-ada']))
            ->assertSee('Kategori transaksi tidak ditemukan')
            ->assertSee('Reset');
    });

    it('membagi daftar menjadi 25 data per halaman', function () {
        KategoriTransaksi::factory()->count(26)->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index'))
            ->assertViewHas('kategoriTransaksi', fn ($paginator) => $paginator->count() === 25 && $paginator->total() === 26)
            ->assertSee('Menampilkan 1–25 dari 26 data');
    });

    it('tidak menampilkan kategori yang sudah dihapus', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Kategori Terhapus'])->delete();

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.index'))
            ->assertDontSee('Kategori Terhapus');
    });
});

describe('tambah', function () {
    it('menampilkan form tambah dengan konfirmasi simpan', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.create'))
            ->assertOk()
            ->assertSee('Tambah Kategori Transaksi')
            ->assertSee('data-confirm-title="Simpan kategori transaksi baru?"', false)
            ->assertSee('data-confirm-variant="success"', false);
    });

    it('memilih jenis transaksi otomatis dari parameter jenis', function () {
        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.create', ['jenis' => 'pemasukan']))
            ->assertSee('<option value="pemasukan" selected', false);
    });

    it('menyimpan kategori transaksi baru', function () {
        $this->actingAs($this->pengguna)
            ->post(route('admin.kategori-transaksi.store'), dataKategoriTransaksi())
            ->assertRedirect(route('admin.kategori-transaksi.index'))
            ->assertSessionHas('success', 'Kategori transaksi berhasil ditambahkan.');

        $kategori = KategoriTransaksi::sole();
        expect($kategori->nama_kategori)->toBe('Domain & Hosting')
            ->and($kategori->jenis_transaksi)->toBe(JenisTransaksi::Pengeluaran)
            ->and($kategori->keterangan)->toBe('Domain, hosting, dan SSL');
    });

    it('menolak data tidak valid dengan pesan berbahasa Indonesia', function (array $timpa, string $kolom, string $pesan) {
        $this->actingAs($this->pengguna)
            ->from(route('admin.kategori-transaksi.create'))
            ->post(route('admin.kategori-transaksi.store'), dataKategoriTransaksi($timpa))
            ->assertRedirect(route('admin.kategori-transaksi.create'))
            ->assertSessionHasErrors([$kolom => $pesan]);

        expect(KategoriTransaksi::count())->toBe(0);
    })->with([
        'nama kosong' => [['nama_kategori' => ''], 'nama_kategori', 'Kolom nama kategori wajib diisi.'],
        'nama terlalu panjang' => [['nama_kategori' => str_repeat('a', 101)], 'nama_kategori', 'Kolom nama kategori tidak boleh lebih dari 100 karakter.'],
        'jenis kosong' => [['jenis_transaksi' => ''], 'jenis_transaksi', 'Kolom jenis transaksi wajib diisi.'],
        'jenis tidak dikenal' => [['jenis_transaksi' => 'transfer'], 'jenis_transaksi', 'Jenis transaksi yang dipilih tidak valid.'],
    ]);

    it('menolak nama kategori yang sudah dipakai pada jenis yang sama', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);

        $this->actingAs($this->pengguna)
            ->post(route('admin.kategori-transaksi.store'), dataKategoriTransaksi())
            ->assertSessionHasErrors(['nama_kategori' => 'Nama kategori sudah dipakai kategori lain dengan jenis yang sama.']);
    });

    it('mengizinkan nama kategori yang sama untuk jenis berbeda', function () {
        KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Lain-lain']);

        $this->actingAs($this->pengguna)
            ->post(route('admin.kategori-transaksi.store'), dataKategoriTransaksi(['nama_kategori' => 'Lain-lain']))
            ->assertSessionHasNoErrors();

        expect(KategoriTransaksi::count())->toBe(2);
    });

    it('mengizinkan nama kategori yang sama dengan kategori terhapus', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting'])->delete();

        $this->actingAs($this->pengguna)
            ->post(route('admin.kategori-transaksi.store'), dataKategoriTransaksi())
            ->assertSessionHasNoErrors();

        expect(KategoriTransaksi::count())->toBe(1);
    });
});

describe('ubah', function () {
    it('menampilkan form ubah berisi data kategori', function () {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Pemasaran']);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.edit', $kategori))
            ->assertOk()
            ->assertSee('Ubah Kategori Transaksi')
            ->assertSee('value="Pemasaran"', false)
            ->assertSee('<option value="pengeluaran" selected', false)
            ->assertSee('data-confirm="Perubahan data kategori “Pemasaran” akan disimpan."', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('name="_method" value="PUT"', false);
    });

    it('memperbarui kategori transaksi', function () {
        $kategori = KategoriTransaksi::factory()->create();

        $this->actingAs($this->pengguna)
            ->put(route('admin.kategori-transaksi.update', $kategori), dataKategoriTransaksi([
                'nama_kategori' => 'Jasa Konsultasi IT',
                'jenis_transaksi' => 'pemasukan',
                'keterangan' => '',
            ]))
            ->assertRedirect(route('admin.kategori-transaksi.index'))
            ->assertSessionHas('success', 'Kategori transaksi berhasil diperbarui.');

        $kategori->refresh();
        expect($kategori->nama_kategori)->toBe('Jasa Konsultasi IT')
            ->and($kategori->jenis_transaksi)->toBe(JenisTransaksi::Pemasukan)
            ->and($kategori->keterangan)->toBeNull();
    });

    it('mengizinkan menyimpan ulang dengan nama kategori sendiri', function () {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);

        $this->actingAs($this->pengguna)
            ->put(route('admin.kategori-transaksi.update', $kategori), dataKategoriTransaksi())
            ->assertSessionHasNoErrors();
    });

    it('menolak nama kategori milik kategori lain dengan jenis yang sama', function () {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Pajak']);
        $kategori = KategoriTransaksi::factory()->create();

        $this->actingAs($this->pengguna)
            ->put(route('admin.kategori-transaksi.update', $kategori), dataKategoriTransaksi(['nama_kategori' => 'Pajak']))
            ->assertSessionHasErrors(['nama_kategori' => 'Nama kategori sudah dipakai kategori lain dengan jenis yang sama.']);
    });

    it('mengembalikan 404 untuk kategori yang sudah dihapus', function () {
        $kategori = KategoriTransaksi::factory()->create();
        $kategori->delete();

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.edit', $kategori))
            ->assertNotFound();
    });
});

describe('hapus', function () {
    it('menghapus kategori transaksi secara soft delete', function () {
        $kategori = KategoriTransaksi::factory()->create();

        $this->actingAs($this->pengguna)
            ->delete(route('admin.kategori-transaksi.destroy', $kategori))
            ->assertRedirect(route('admin.kategori-transaksi.index'))
            ->assertSessionHas('success', 'Kategori transaksi berhasil dihapus.');

        $this->assertSoftDeleted($kategori);
    });

    it('menolak menghapus kategori yang sudah dipakai transaksi', function () {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Pajak']);
        TransaksiKas::factory()->create(['id_kategori_transaksi' => $kategori]);

        $this->actingAs($this->pengguna)
            ->delete(route('admin.kategori-transaksi.destroy', $kategori))
            ->assertRedirect(route('admin.kategori-transaksi.index'))
            ->assertSessionHas('error', 'Kategori “Pajak” tidak dapat dihapus karena sudah dipakai transaksi.');

        $this->assertNotSoftDeleted($kategori);
    });
});

describe('kunci jenis', function () {
    it('mengunci jenis kategori yang sudah dipakai transaksi', function () {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Pajak']);
        TransaksiKas::factory()->create(['id_kategori_transaksi' => $kategori]);

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.edit', $kategori))
            ->assertSee('Terkunci karena kategori sudah dipakai transaksi.')
            ->assertDontSee('<option value="pemasukan"', false);

        $this->put(route('admin.kategori-transaksi.update', $kategori), dataKategoriTransaksi([
            'nama_kategori' => 'Pajak',
            'jenis_transaksi' => 'pemasukan',
        ]))->assertSessionHasErrors(['jenis_transaksi' => 'Jenis transaksi tidak dapat diubah karena kategori sudah dipakai transaksi.']);

        $this->put(route('admin.kategori-transaksi.update', $kategori), dataKategoriTransaksi([
            'nama_kategori' => 'Pajak & Retribusi',
            'jenis_transaksi' => 'pengeluaran',
        ]))->assertSessionHasNoErrors();

        expect($kategori->refresh()->nama_kategori)->toBe('Pajak & Retribusi');
    });

    it('membiarkan jenis kategori yang belum dipakai diubah', function () {
        $kategori = KategoriTransaksi::factory()->create();

        $this->actingAs($this->pengguna)
            ->get(route('admin.kategori-transaksi.edit', $kategori))
            ->assertSee('<option value="pemasukan"', false);
    });
});
