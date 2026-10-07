<?php

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\KategoriTransaksi;
use Database\Seeders\KategoriTransaksiSeeder;

it('membuat 18 kategori awal sesuai jenisnya', function () {
    $this->seed(KategoriTransaksiSeeder::class);

    expect(KategoriTransaksi::count())->toBe(18)
        ->and(KategoriTransaksi::where('jenis_transaksi', JenisTransaksi::Pemasukan)->count())->toBe(7)
        ->and(KategoriTransaksi::where('jenis_transaksi', JenisTransaksi::Pengeluaran)->count())->toBe(11);

    $this->assertDatabaseHas('tb_kategori_transaksi', ['nama_kategori' => 'Domain & Hosting', 'jenis_transaksi' => 'pengeluaran']);
    $this->assertDatabaseHas('tb_kategori_transaksi', ['nama_kategori' => 'Setoran Modal Pemilik', 'jenis_transaksi' => 'pemasukan']);
});

it('aman dijalankan ulang tanpa menggandakan atau menimpa kategori', function () {
    $this->seed(KategoriTransaksiSeeder::class);
    KategoriTransaksi::where('nama_kategori', 'Pajak')->update(['keterangan' => 'PPh & PPN']);

    $this->seed(KategoriTransaksiSeeder::class);

    expect(KategoriTransaksi::count())->toBe(18)
        ->and(KategoriTransaksi::where('nama_kategori', 'Pajak')->value('keterangan'))->toBe('PPh & PPN');
});

it('tidak membuat ulang kategori yang sudah dihapus admin', function () {
    $this->seed(KategoriTransaksiSeeder::class);
    KategoriTransaksi::where('nama_kategori', 'Bootcamp')->sole()->delete();

    $this->seed(KategoriTransaksiSeeder::class);

    expect(KategoriTransaksi::count())->toBe(17)
        ->and(KategoriTransaksi::withTrashed()->count())->toBe(18);
});
