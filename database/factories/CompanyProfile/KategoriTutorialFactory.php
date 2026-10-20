<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriTutorial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            // Dari nama akhir (termasuk nama yang ditimpa test), seperti slug buatan KategoriTutorialService.
            'slug' => fn (array $atribut): string => Str::slug($atribut['nama_kategori']),
        ];
    }
}
