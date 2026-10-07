<?php

namespace Database\Factories\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\KategoriTransaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriTransaksi>
 */
class KategoriTransaksiFactory extends Factory
{
    protected $model = KategoriTransaksi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => 'Biaya '.fake()->unique()->words(2, true),
            'jenis_transaksi' => JenisTransaksi::Pengeluaran,
            'keterangan' => null,
        ];
    }

    public function pemasukan(): static
    {
        return $this->state(fn () => [
            'nama_kategori' => 'Pendapatan '.fake()->unique()->words(2, true),
            'jenis_transaksi' => JenisTransaksi::Pemasukan,
        ]);
    }
}
