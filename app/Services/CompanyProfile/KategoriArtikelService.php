<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\Shared\SlugService;

class KategoriArtikelService
{
    public function __construct(private readonly SlugService $slug) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreKategoriArtikelRequest
     */
    public function simpan(array $data): KategoriArtikel
    {
        return KategoriArtikel::create([
            ...$data,
            'slug' => $this->buatSlug($data['nama_kategori']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateKategoriArtikelRequest
     */
    public function perbarui(KategoriArtikel $kategoriArtikel, array $data): KategoriArtikel
    {
        // Sama dengan slug artikel: mengikuti nama hanya selama halaman kategorinya belum tayang (belum ada artikel
        // terbit); setelah itu dikunci agar tautan yang sudah tersebar tidak rusak (pilihan pemilik 2026-10-15).
        if ($data['nama_kategori'] !== $kategoriArtikel->nama_kategori && ! $kategoriArtikel->slugTerkunci()) {
            $data['slug'] = $this->buatSlug($data['nama_kategori'], $kategoriArtikel);
        }

        $kategoriArtikel->update($data);

        return $kategoriArtikel;
    }

    /**
     * Slug dari nama, diberi akhiran angka bila sudah dipakai kategori lain —
     * termasuk yang terhapus, agar tidak bentrok saat kelak dipulihkan.
     */
    public function buatSlug(string $nama, ?KategoriArtikel $kecuali = null): string
    {
        return $this->slug->buat($nama, KategoriArtikel::withTrashed(), $kecuali, 'kategori');
    }
}
