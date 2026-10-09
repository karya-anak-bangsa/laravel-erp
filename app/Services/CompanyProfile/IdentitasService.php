<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Identitas;
use App\Services\Shared\FileUploadService;
use Illuminate\Support\Facades\Cache;

class IdentitasService
{
    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * Identitas untuk frontend publik, di-cache tanpa batas waktu dan dibuang otomatis lewat event model
     * Identitas. Yang disimpan berupa array atribut, bukan objek: store database di produksi memakai
     * cache.serializable_classes=false sehingga objek Eloquent akan rusak saat dibaca ulang.
     */
    public function untukSitus(): Identitas
    {
        /** @var array<string, mixed> $atribut */
        $atribut = Cache::rememberForever(Identitas::KUNCI_CACHE, fn (): array => Identitas::tunggal()->getAttributes());

        return (new Identitas)->newFromBuilder($atribut);
    }

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
