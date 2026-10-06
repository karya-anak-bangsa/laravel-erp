<?php

namespace Database\Factories\Kas;

use App\Enums\Kas\JenisAkunKas;
use App\Models\Kas\AkunKas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AkunKas>
 */
class AkunKasFactory extends Factory
{
    protected $model = AkunKas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_akun' => 'Rekening '.fake()->unique()->company(),
            'jenis_akun' => JenisAkunKas::Bank,
            'nama_bank' => fake()->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI', 'BSI']),
            'nomor_rekening' => fake()->numerify('##########'),
            'saldo_awal' => fake()->numberBetween(0, 50_000_000),
            'tanggal_saldo_awal' => fake()->dateTimeBetween('-1 year'),
            'status_aktif' => true,
            'keterangan' => null,
        ];
    }

    public function tunai(): static
    {
        return $this->state(fn () => [
            'nama_akun' => 'Kas Tunai '.fake()->unique()->city(),
            'jenis_akun' => JenisAkunKas::Tunai,
            'nama_bank' => null,
            'nomor_rekening' => null,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status_aktif' => false]);
    }
}
