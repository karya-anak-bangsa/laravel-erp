<?php

namespace Database\Factories\CompanyProfile;

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\KetebalanJudul;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use App\Models\CompanyProfile\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    /**
     * Bawaan: template Full Color nonaktif.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Template '.fake()->unique()->word().' '.fake()->word(),
            'jenis' => JenisTemplate::FullColor,
            'warna_utama' => fake()->hexColor(),
            'warna_aksen' => fake()->hexColor(),
            'nada_dasar' => null,
            'font' => FontTemplate::Geist,
            'sudut' => SudutTemplate::Sedang,
            'skala' => SkalaTemplate::Ringkas,
            'lebar_konten' => LebarKonten::Lebar,
            'ketebalan_judul' => KetebalanJudul::Semibold,
            'bayangan' => BayanganTemplate::Halus,
            'status_aktif' => false,
        ];
    }

    public function monochrome(): static
    {
        return $this->state([
            'jenis' => JenisTemplate::Monochrome,
            'warna_utama' => null,
            'warna_aksen' => null,
            'nada_dasar' => NadaDasar::Netral,
        ]);
    }

    public function aktif(): static
    {
        return $this->state(['status_aktif' => true]);
    }
}
