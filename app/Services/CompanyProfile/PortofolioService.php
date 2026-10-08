<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Portofolio;
use App\Services\Shared\FileUploadService;
use App\Services\Shared\SlugService;

class PortofolioService
{
    public function __construct(
        private readonly FileUploadService $fileUpload,
        private readonly SlugService $slug,
    ) {}

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
        return $this->slug->buat($judul, Portofolio::withTrashed(), $kecuali, 'portofolio');
    }
}
