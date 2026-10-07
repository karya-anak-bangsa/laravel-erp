<?php

namespace Database\Seeders;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\KategoriTransaksi;
use Illuminate\Database\Seeder;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Kategori awal sesuai docs/DATABASE.md.
     *
     * @var array<string, list<string>>
     */
    public const KATEGORI = [
        'pemasukan' => [
            'Setoran Modal Pemilik',
            'Jasa Pembuatan Website',
            'Jasa Mobile Apps',
            'Pelatihan IT',
            'Sertifikasi IT',
            'Bootcamp',
            'Pendapatan Lain-lain',
        ],
        'pengeluaran' => [
            'Perizinan & Legalitas',
            'Domain & Hosting',
            'Perangkat Lunak & Lisensi',
            'Perangkat Keras',
            'Operasional Kantor',
            'Pemasaran',
            'Honor & Gaji',
            'Transportasi',
            'Pajak',
            'Biaya Administrasi Bank',
            'Pengeluaran Lain-lain',
        ],
    ];

    public function run(): void
    {
        foreach (self::KATEGORI as $jenis => $daftarNama) {
            foreach ($daftarNama as $nama) {
                // withTrashed agar kategori yang sengaja dihapus admin tidak dibuat ulang saat seeder dijalankan lagi.
                KategoriTransaksi::withTrashed()->firstOrCreate([
                    'nama_kategori' => $nama,
                    'jenis_transaksi' => JenisTransaksi::from($jenis),
                ]);
            }
        }
    }
}
