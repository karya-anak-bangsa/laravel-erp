<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\Layanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Layanan>
 */
class LayananFactory extends Factory
{
    protected $model = Layanan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->unique()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'gambar' => Layanan::FOLDER.'/'.fake()->uuid().'.webp',
            'keterangan' => fake()->optional()->paragraphs(2, true),
            'urutan_ke' => fake()->numberBetween(1, 20),
        ];
    }
}
