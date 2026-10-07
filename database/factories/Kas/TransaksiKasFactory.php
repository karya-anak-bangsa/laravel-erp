<?php

namespace Database\Factories\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<TransaksiKas>
 */
class TransaksiKasFactory extends Factory
{
    protected $model = TransaksiKas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_akun_kas' => AkunKas::factory()->state(['tanggal_saldo_awal' => '2026-01-01']),
            'id_kategori_transaksi' => KategoriTransaksi::factory(),
            // Jenis selalu mengikuti kategori, sama seperti aturan di TransaksiKasService.
            'jenis_transaksi' => fn (array $atribut) => KategoriTransaksi::findOrFail($atribut['id_kategori_transaksi'])->jenis_transaksi,
            'tanggal_transaksi' => fake()->dateTimeBetween('2026-01-01', 'now')->format('Y-m-d'),
            // Urutan 9xxx agar tidak bentrok dengan nomor 0001 dst. yang dibuat Service di test.
            'nomor_transaksi' => fn (array $atribut) => JenisTransaksi::from(
                $atribut['jenis_transaksi'] instanceof JenisTransaksi ? $atribut['jenis_transaksi']->value : $atribut['jenis_transaksi']
            )->awalanNomor()
                .'-'.Carbon::parse($atribut['tanggal_transaksi'])->format('Ym')
                .'-'.fake()->unique()->numerify('9###'),
            'jumlah' => fake()->numberBetween(10, 5_000) * 1_000,
            'nama_pihak' => fake()->company(),
            'keterangan' => fake()->sentence(),
            'bukti_transaksi' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function pemasukan(): static
    {
        return $this->state(fn () => [
            'id_kategori_transaksi' => KategoriTransaksi::factory()->pemasukan(),
        ]);
    }
}
