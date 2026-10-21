<?php

namespace Database\Factories\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artikel>
 */
class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = rtrim(fake()->unique()->sentence(5), '.');

        return [
            'id_kategori_artikel' => KategoriArtikel::factory(),
            'judul' => $judul,
            'slug' => Str::slug($judul),
            'deskripsi' => Collection::times(3, fn (): string => '<p>'.fake()->paragraph().'</p>')->implode(''),
            'gambar' => Artikel::FOLDER.'/'.fake()->uuid().'.webp',
            'tanggal' => fake()->dateTimeBetween('-60 days')->format('Y-m-d'),
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
}
