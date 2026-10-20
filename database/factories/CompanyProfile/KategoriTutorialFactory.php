<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriTutorial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriTutorial>
 */
class KategoriTutorialFactory extends Factory
{
    protected $model = KategoriTutorial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => ucwords(fake()->unique()->words(2, true)),
        ];
    }
}
