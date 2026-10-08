<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Layanan;
use App\Services\Shared\FileUploadService;

class LayananService
{
    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreLayananRequest
     */
    public function simpan(array $data): Layanan
    {
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar'], Layanan::FOLDER, Layanan::DISK,
            fn (array $data): Layanan => Layanan::create($data),
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateLayananRequest
     */
    public function perbarui(Layanan $layanan, array $data): Layanan
    {
        return $this->fileUpload->simpanDenganBerkas($layanan, $data, ['gambar'], Layanan::FOLDER, Layanan::DISK,
            function (array $data) use ($layanan): Layanan {
                $layanan->update($data);

                return $layanan;
            },
        );
    }
}
