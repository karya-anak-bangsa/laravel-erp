<?php

namespace Database\Factories\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tutorial>
 */
class TutorialFactory extends Factory
{
    protected $model = Tutorial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = 'Tutorial '.ucwords(fake()->unique()->words(3, true));

        return [
            'id_kategori_tutorial' => KategoriTutorial::factory(),
            'judul' => $judul,
            'slug' => Str::slug($judul),
            'deskripsi' => '<p>'.fake()->paragraph().'</p>',
            'gambar' => Tutorial::FOLDER.'/'.fake()->uuid().'.webp',
            'status_publikasi' => fake()->randomElement(StatusPublikasi::cases()),
        ];
    }

    public function draf(): static
    {
        return $this->state(['status_publikasi' => StatusPublikasi::Draf]);
    }

    public function terbit(): static
    {
        return $this->state(['status_publikasi' => StatusPublikasi::Terbit]);
    }

    public function kategori(string $nama): static
    {
        return $this->state(fn (): array => [
            'id_kategori_tutorial' => KategoriTutorial::firstOrCreate(['nama_kategori' => $nama])->id_kategori_tutorial,
        ]);
    }
}
