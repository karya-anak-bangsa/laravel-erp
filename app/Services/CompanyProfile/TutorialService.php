<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\Shared\FileUploadService;
use App\Services\Shared\SlugService;
use Illuminate\Support\Facades\DB;

class TutorialService
{
    public function __construct(
        private readonly FileUploadService $fileUpload,
        private readonly SlugService $slug,
    ) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreTutorialRequest
     */
    public function simpan(array $data): Tutorial
    {
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar'], Tutorial::FOLDER, Tutorial::DISK,
            fn (array $data): Tutorial => Tutorial::create([
                ...$data,
                'slug' => $this->buatSlug($data['judul']),
            ]),
            optimalkanGambar: true,
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateTutorialRequest
     */
    public function perbarui(Tutorial $tutorial, array $data): Tutorial
    {
        return $this->fileUpload->simpanDenganBerkas($tutorial, $data, ['gambar'], Tutorial::FOLDER, Tutorial::DISK,
            function (array $data) use ($tutorial): Tutorial {
                // Sama dengan artikel: slug mengikuti judul hanya selama tutorial masih draf; setelah terbit dikunci
                // agar tautan yang sudah dibagikan tidak rusak.
                if ($data['judul'] !== $tutorial->judul && $tutorial->status_publikasi === StatusPublikasi::Draf) {
                    $data['slug'] = $this->buatSlug($data['judul'], $tutorial);
                }

                $tutorial->update($data);

                return $tutorial;
            },
            optimalkanGambar: true,
        );
    }

    /**
     * Materi bagian dari tutorialnya, jadi ikut terhapus (soft delete) dalam satu transaksi; gambar sampul tetap
     * disimpan seperti data terhapus lainnya.
     */
    public function hapus(Tutorial $tutorial): void
    {
        DB::transaction(function () use ($tutorial): void {
            MateriTutorial::query()->where('id_tutorial', $tutorial->id_tutorial)->delete();
            $tutorial->delete();
        });
    }

    /**
     * Slug dari judul, diberi akhiran angka bila sudah dipakai tutorial lain —
     * termasuk yang terhapus, agar tidak bentrok saat kelak dipulihkan. Slug "kategori" dicadangkan: alamat materi
     * /tutorial/kategori/{materi} akan tertutup halaman kategori /tutorial/kategori/{slug}.
     */
    public function buatSlug(string $judul, ?Tutorial $kecuali = null): string
    {
        return $this->slug->buat($judul, Tutorial::withTrashed(), $kecuali, 'tutorial', terlarang: ['kategori']);
    }
}
