<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriArtikel>
 */
class KategoriArtikelFactory extends Factory
{
    protected $model = KategoriArtikel::class;

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
