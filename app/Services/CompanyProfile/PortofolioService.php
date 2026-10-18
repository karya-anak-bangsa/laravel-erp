<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\GambarPortofolio;
use App\Models\CompanyProfile\Portofolio;
use App\Services\Shared\FileUploadService;
use App\Services\Shared\SlugService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        return $this->fileUpload->simpanDenganBerkas(null, $data, ['gambar', 'galeri'], Portofolio::FOLDER, Portofolio::DISK,
            fn (array $data): Portofolio => DB::transaction(function () use ($data): Portofolio {
                $portofolio = Portofolio::create([
                    ...Arr::except($data, 'galeri'),
                    'slug' => $this->buatSlug($data['judul']),
                ]);
                $this->tambahGambar($portofolio, $data['galeri'] ?? []);

                return $portofolio;
            }),
        );
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdatePortofolioRequest; galeri_lama = id gambar tambahan lama
     *                                      yang dipertahankan (tanpa kunci ini semua gambar tambahan lama tetap)
     */
    public function perbarui(Portofolio $portofolio, array $data): Portofolio
    {
        // Dicatat sebelum disimpan: berkasnya baru dihapus setelah data tersimpan, sama dengan gambar utama yang diganti.
        $dibuang = array_key_exists('galeri_lama', $data)
            ? $portofolio->gambarPortofolio()->whereKeyNot(Arr::wrap($data['galeri_lama']))->get()
            : new Collection;

        // Slug tidak ikut judul: portofolio tanpa draf langsung tayang, jadi tautannya mungkin sudah tersebar
        // (pilihan pemilik 2026-10-18).
        $hasil = $this->fileUpload->simpanDenganBerkas($portofolio, Arr::except($data, 'galeri_lama'), ['gambar', 'galeri'], Portofolio::FOLDER, Portofolio::DISK,
            fn (array $data): Portofolio => DB::transaction(function () use ($portofolio, $data, $dibuang): Portofolio {
                $portofolio->update(Arr::except($data, 'galeri'));
                GambarPortofolio::query()->whereKey($dibuang->modelKeys())->delete();
                $this->tambahGambar($portofolio, $data['galeri'] ?? []);

                return $portofolio;
            }),
        );

        foreach ($dibuang as $gambar) {
            $this->fileUpload->hapus($gambar->gambar, Portofolio::DISK);
        }

        return $hasil;
    }

    /**
     * Slug dari judul, diberi akhiran angka bila sudah dipakai portofolio lain —
     * termasuk yang terhapus, agar tidak bentrok saat kelak dipulihkan.
     */
    public function buatSlug(string $judul): string
    {
        return $this->slug->buat($judul, Portofolio::withTrashed(), cadangan: 'portofolio');
    }

    /**
     * @param  list<string>  $daftarPath  path gambar tambahan yang sudah tersimpan, urut unggah
     */
    private function tambahGambar(Portofolio $portofolio, array $daftarPath): void
    {
        foreach ($daftarPath as $path) {
            $portofolio->gambarPortofolio()->create(['gambar' => $path]);
        }
    }
}
