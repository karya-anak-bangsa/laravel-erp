<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MateriTutorial>
 */
class MateriTutorialFactory extends Factory
{
    protected $model = MateriTutorial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_tutorial' => Tutorial::factory(),
            'judul' => rtrim(fake()->unique()->sentence(4), '.'),
            // Dari judul akhir (termasuk judul yang ditimpa test), seperti slug buatan MateriTutorialService.
            'slug' => fn (array $atribut): string => Str::slug($atribut['judul']),
            'deskripsi' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'urutan_ke' => fake()->numberBetween(1, 20),
        ];
    }
}
