<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\GambarPortofolio;
use App\Models\CompanyProfile\Portofolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GambarPortofolio>
 */
class GambarPortofolioFactory extends Factory
{
    protected $model = GambarPortofolio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_portofolio' => Portofolio::factory(),
            'gambar' => Portofolio::FOLDER.'/'.fake()->uuid().'.webp',
        ];
    }
}
