<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            // Dari nama akhir (termasuk nama yang ditimpa test), seperti slug buatan KategoriArtikelService.
            'slug' => fn (array $atribut): string => Str::slug($atribut['nama_kategori']),
        ];
    }
}
