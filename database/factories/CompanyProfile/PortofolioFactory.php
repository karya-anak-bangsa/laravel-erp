<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\Portofolio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
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
            'id_kategori_portofolio' => KategoriPortofolio::factory(),
            'judul' => $judul,
            'slug' => Str::slug($judul),
            'deskripsi' => Collection::times(2, fn (): string => '<p>'.fake()->paragraph().'</p>')->implode(''),
            'gambar' => Portofolio::FOLDER.'/'.fake()->uuid().'.webp',
        ];
    }

    /**
     * Kategori berdasarkan nama, dipakai bersama bila sudah ada (mis. dari KategoriPortofolioSeeder).
     */
    public function kategori(string $nama): static
    {
        return $this->state(fn (): array => [
            'id_kategori_portofolio' => KategoriPortofolio::firstOrCreate(['nama_kategori' => $nama])->id_kategori_portofolio,
        ]);
    }
}
