<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\Layanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

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
            'deskripsi' => '<p>'.fake()->paragraph().'</p>',
            'gambar' => Layanan::FOLDER.'/'.fake()->uuid().'.webp',
            'keterangan' => fake()->optional()->passthrough(Collection::times(2, fn (): string => '<p>'.fake()->paragraph().'</p>')->implode('')),
            'urutan_ke' => fake()->numberBetween(1, 20),
        ];
    }
}
