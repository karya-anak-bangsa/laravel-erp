<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Identitas;
use App\Services\Shared\FileUploadService;

class IdentitasService
{
    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * Memperbarui identitas; logo/favicon hanya diganti bila ada berkas baru.
     *
     * @param  array<string, mixed>  $data  hasil validasi UpdateIdentitasRequest
     */
    public function perbarui(Identitas $identitas, array $data): Identitas
    {
        return $this->fileUpload->simpanDenganBerkas(
            $identitas,
            $data,
            ['logo_website', 'favicon_website'],
            Identitas::FOLDER,
            Identitas::DISK,
            function (array $data) use ($identitas): Identitas {
                $identitas->update($data);

                return $identitas;
            },
        );
    }
}
