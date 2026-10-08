<?php

namespace Database\Factories\CompanyProfile;

use App\Enums\CompanyProfile\GayaCta;
use App\Models\CompanyProfile\Hero;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    protected $model = Hero::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(5),
            'deskripsi' => fake()->paragraph(),
            'gambar' => Hero::FOLDER.'/'.fake()->uuid().'.webp',
            'keyword' => fake()->words(3),
            'cta' => [
                ['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => GayaCta::Primary->value],
            ],
            'status_aktif' => false,
        ];
    }

    public function aktif(): static
    {
        return $this->state(['status_aktif' => true]);
    }
}
