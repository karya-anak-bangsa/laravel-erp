<?php

namespace Database\Factories\CompanyProfile;

use App\Models\CompanyProfile\Identitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Identitas>
 */
class IdentitasFactory extends Factory
{
    protected $model = Identitas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_perusahaan' => fake()->company(),
            'judul_website' => fake()->sentence(4),
            'alamat_website' => fake()->url(),
            'meta_deskripsi' => fake()->sentence(),
            'meta_keyword' => implode(', ', fake()->words(4)),
            'logo_website' => Identitas::FOLDER.'/'.fake()->uuid().'.png',
            'favicon_website' => Identitas::FOLDER.'/'.fake()->uuid().'.png',
            'email' => fake()->companyEmail(),
            'telepon' => '0812'.fake()->numerify('########'),
            'alamat' => fake()->address(),
            'link_youtube' => 'https://www.youtube.com/@'.fake()->userName(),
            'link_instagram' => 'https://www.instagram.com/'.fake()->userName(),
            'link_whatsapp' => 'https://wa.me/62812'.fake()->numerify('########'),
        ];
    }
}
