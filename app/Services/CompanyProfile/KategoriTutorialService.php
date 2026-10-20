<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\KategoriTutorial;
use App\Services\Shared\SlugService;

class KategoriTutorialService
{
    public function __construct(private readonly SlugService $slug) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreKategoriTutorialRequest
     */
    public function simpan(array $data): KategoriTutorial
    {
        return KategoriTutorial::create([
            ...$data,
            'slug' => $this->buatSlug($data['nama_kategori']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateKategoriTutorialRequest
     */
    public function perbarui(KategoriTutorial $kategoriTutorial, array $data): KategoriTutorial
    {
        // Sama dengan slug kategori artikel: mengikuti nama hanya selama halaman kategorinya belum tayang (belum ada
        // tutorial tayang); setelah itu dikunci agar tautan yang sudah tersebar tidak rusak.
        if ($data['nama_kategori'] !== $kategoriTutorial->nama_kategori && ! $kategoriTutorial->slugTerkunci()) {
            $data['slug'] = $this->buatSlug($data['nama_kategori'], $kategoriTutorial);
        }

        $kategoriTutorial->update($data);

        return $kategoriTutorial;
    }

    /**
     * Slug dari nama, diberi akhiran angka bila sudah dipakai kategori lain — termasuk yang terhapus, agar tidak bentrok
     * saat kelak dipulihkan.
     */
    public function buatSlug(string $nama, ?KategoriTutorial $kecuali = null): string
    {
        return $this->slug->buat($nama, KategoriTutorial::withTrashed(), $kecuali, 'kategori');
    }
}
