<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Hero;
use App\Services\Shared\FileUploadService;
use Illuminate\Support\Facades\DB;

class HeroService
{
    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreHeroRequest
     */
    public function simpan(array $data): Hero
    {
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar'], Hero::FOLDER, Hero::DISK,
            fn (array $data): Hero => DB::transaction(function () use ($data): Hero {
                $hero = Hero::create($data);
                $this->nonaktifkanHeroLain($hero);

                return $hero;
            }),
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateHeroRequest
     */
    public function perbarui(Hero $hero, array $data): Hero
    {
        return $this->fileUpload->simpanDenganBerkas($hero, $data, ['gambar'], Hero::FOLDER, Hero::DISK,
            fn (array $data): Hero => DB::transaction(function () use ($hero, $data): Hero {
                $hero->update($data);
                $this->nonaktifkanHeroLain($hero);

                return $hero;
            }),
        );
    }

    // Boleh banyak hero, tetapi hanya satu yang aktif (keputusan pemilik): beranda
    // selalu jelas menampilkan hero mana.
    private function nonaktifkanHeroLain(Hero $hero): void
    {
        if (! $hero->status_aktif) {
            return;
        }

        // Termasuk yang terhapus agar tidak ada dua hero aktif bila kelak dipulihkan dari sampah.
        Hero::withTrashed()
            ->whereKeyNot($hero->getKey())
            ->where('status_aktif', true)
            ->update(['status_aktif' => false]);
    }
}
