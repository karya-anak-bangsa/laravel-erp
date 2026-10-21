<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriPortofolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriPortofolio>
 */
class KategoriPortofolioFactory extends Factory
{
    protected $model = KategoriPortofolio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kategori' => ucwords(fake()->unique()->word().' '.fake()->word()),
        ];
    }
}
