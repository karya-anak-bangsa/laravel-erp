<?php

namespace Database\Seeders;

use App\Enums\CompanyProfile\JenisTemplate;
use App\Models\CompanyProfile\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TemplateSeeder extends Seeder
{
    /**
     * Dua template bawaan yang aktif, satu per jenis. Nilainya diambil dari config/tema.php, yang juga
     * menjadi cadangan TemaService bila database tidak terbaca, agar keduanya tidak pernah berbeda.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (Template::withTrashed()->exists()) {
            return;
        }

        foreach (JenisTemplate::cases() as $jenis) {
            /** @var array<string, string|null> $nilai */
            $nilai = config("tema.template.{$jenis->value}");

            Template::create([
                ...Arr::only($nilai, Template::KOLOM_TAMPILAN),
                'jenis' => $jenis,
                // Hex disimpan huruf kecil, sama seperti hasil form admin.
                'warna_utama' => isset($nilai['warna_utama']) ? strtolower($nilai['warna_utama']) : null,
                'warna_aksen' => isset($nilai['warna_aksen']) ? strtolower($nilai['warna_aksen']) : null,
                'status_aktif' => true,
            ]);
        }
    }
}
