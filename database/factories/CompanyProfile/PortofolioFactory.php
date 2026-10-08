<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\Portofolio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Portofolio>
 */
class PortofolioFactory extends Factory
{
    protected $model = Portofolio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = fake()->unique()->sentence(4);

        return [
            'judul' => $judul,
            'slug' => Str::slug($judul),
            'deskripsi' => fake()->paragraphs(2, true),
            'gambar' => Portofolio::FOLDER.'/'.fake()->uuid().'.webp',
            'kategori' => fake()->randomElement(['Website', 'Mobile Apps', 'Pelatihan IT']),
        ];
    }
}
