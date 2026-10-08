<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Portofolio;
use App\Services\Shared\FileUploadService;
use Illuminate\Support\Str;

class PortofolioService
{
    // Disisakan ruang dari VARCHAR(220) untuk akhiran angka (-2, -3, ...).
    private const PANJANG_SLUG_MAKS = 200;

    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StorePortofolioRequest
     */
    public function simpan(array $data): Portofolio
    {
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar'], Portofolio::FOLDER, Portofolio::DISK,
            fn (array $data): Portofolio => Portofolio::create([
                ...$data,
                'slug' => $this->buatSlug($data['judul']),
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdatePortofolioRequest
     */
    public function perbarui(Portofolio $portofolio, array $data): Portofolio
    {
        return $this->fileUpload->simpanDenganBerkas($portofolio, $data, ['gambar'], Portofolio::FOLDER, Portofolio::DISK,
            function (array $data) use ($portofolio): Portofolio {
                // Slug mengikuti judul; frontend belum tayang sehingga belum ada tautan yang rusak (tinjau di Fase 4).
                if ($data['judul'] !== $portofolio->judul) {
                    $data['slug'] = $this->buatSlug($data['judul'], $portofolio);
                }

                $portofolio->update($data);

                return $portofolio;
            },
        );
    }

    /**
     * Slug dari judul, diberi akhiran angka bila sudah dipakai portofolio lain —
     * termasuk yang terhapus, agar tidak bentrok saat kelak dipulihkan.
     */
    public function buatSlug(string $judul, ?Portofolio $kecuali = null): string
    {
        $dasar = rtrim(Str::substr(Str::slug($judul), 0, self::PANJANG_SLUG_MAKS), '-');

        if ($dasar === '') {
            $dasar = 'portofolio';
        }

        $slug = $dasar;

        for ($nomor = 2; $this->slugDipakai($slug, $kecuali); $nomor++) {
            $slug = "{$dasar}-{$nomor}";
        }

        return $slug;
    }

    private function slugDipakai(string $slug, ?Portofolio $kecuali): bool
    {
        return Portofolio::withTrashed()
            ->where('slug', $slug)
            ->when($kecuali !== null, fn ($query) => $query->whereKeyNot($kecuali->getKey()))
            ->exists();
    }
}
