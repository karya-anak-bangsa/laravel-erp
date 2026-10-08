<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Artikel;
use App\Services\Shared\FileUploadService;
use App\Services\Shared\SlugService;

class ArtikelService
{
    public function __construct(
        private readonly FileUploadService $fileUpload,
        private readonly SlugService $slug,
    ) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreArtikelRequest
     */
    public function simpan(array $data): Artikel
    {
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar'], Artikel::FOLDER, Artikel::DISK,
            fn (array $data): Artikel => Artikel::create([
                ...$data,
                'slug' => $this->buatSlug($data['judul']),
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateArtikelRequest
     */
    public function perbarui(Artikel $artikel, array $data): Artikel
    {
        return $this->fileUpload->simpanDenganBerkas($artikel, $data, ['gambar'], Artikel::FOLDER, Artikel::DISK,
            function (array $data) use ($artikel): Artikel {
                // Slug mengikuti judul; frontend belum tayang sehingga belum ada tautan yang rusak (tinjau di Fase 4).
                if ($data['judul'] !== $artikel->judul) {
                    $data['slug'] = $this->buatSlug($data['judul'], $artikel);
                }

                $artikel->update($data);

                return $artikel;
            },
        );
    }

    /**
     * Slug dari judul, diberi akhiran angka bila sudah dipakai artikel lain —
     * termasuk yang terhapus, agar tidak bentrok saat kelak dipulihkan.
     */
    public function buatSlug(string $judul, ?Artikel $kecuali = null): string
    {
        return $this->slug->buat($judul, Artikel::withTrashed(), $kecuali, 'artikel');
    }
}
