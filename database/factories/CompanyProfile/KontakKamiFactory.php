<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KontakKami;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KontakKami>
 */
class KontakKamiFactory extends Factory
{
    protected $model = KontakKami::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subjek' => rtrim(fake()->sentence(4), '.'),
            'pesan' => implode("\n\n", fake()->paragraphs(2)),
            'tanggal' => fake()->dateTimeBetween('-30 days'),
            'status_baca' => fake()->boolean(),
        ];
    }

    public function dibaca(): static
    {
        return $this->state(['status_baca' => true]);
    }

    public function belumDibaca(): static
    {
        return $this->state(['status_baca' => false]);
    }
}
