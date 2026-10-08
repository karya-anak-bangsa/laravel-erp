<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\Identitas;
use App\Services\Shared\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Throwable;

class IdentitasService
{
    private const KOLOM_BERKAS = ['logo_website', 'favicon_website'];

    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * Memperbarui identitas; logo/favicon hanya diganti bila ada berkas baru.
     *
     * @param  array<string, mixed>  $data  hasil validasi UpdateIdentitasRequest
     */
    public function perbarui(Identitas $identitas, array $data): Identitas
    {
        $berkasBaru = [];
        $berkasLama = [];

        try {
            foreach (self::KOLOM_BERKAS as $kolom) {
                if (($data[$kolom] ?? null) instanceof UploadedFile) {
                    $berkasBaru[$kolom] = $this->fileUpload->simpan($data[$kolom], Identitas::FOLDER, Identitas::DISK);
                    $berkasLama[$kolom] = $identitas->getAttribute($kolom);
                }
            }

            $identitas->update([...Arr::except($data, self::KOLOM_BERKAS), ...$berkasBaru]);
        } catch (Throwable $e) {
            // Berkas baru yang sudah terunggah tidak dirujuk data mana pun bila penyimpanan gagal.
            foreach ($berkasBaru as $path) {
                $this->fileUpload->hapus($path, Identitas::DISK);
            }

            throw $e;
        }

        // File lama baru dihapus setelah data tersimpan agar tidak ada rujukan ke file yang hilang.
        foreach ($berkasLama as $path) {
            $this->fileUpload->hapus($path, Identitas::DISK);
        }

        return $identitas;
    }
}
