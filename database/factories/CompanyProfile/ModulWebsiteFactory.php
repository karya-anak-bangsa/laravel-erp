<?php

namespace Database\Factories\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModulWebsite>
 */
class ModulWebsiteFactory extends Factory
{
    protected $model = ModulWebsite::class;

    /**
     * Bawaan: modul aktif dengan urutan bawaan jenisnya. Jenis unik, jadi test biasanya mengisinya sendiri.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis' => fake()->unique()->randomElement(JenisModul::cases()),
            'urutan_ke' => fn (array $atribut): int => JenisModul::from(
                $atribut['jenis'] instanceof JenisModul ? $atribut['jenis']->value : $atribut['jenis'],
            )->urutanBawaan(),
            'status_aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['status_aktif' => false]);
    }
}
