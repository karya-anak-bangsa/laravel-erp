<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Ringkasan modul Company Profile untuk dashboard admin. Hanya membaca tabel yang sudah ada (pilihan pemilik
 * 2026-10-20: tanpa tabel riwayat); modul lain kelak menyediakan ringkasannya sendiri.
 */
class RingkasanService
{
    /**
     * Angka per modul untuk kartu dashboard. Data terhapus (soft delete) tidak dihitung.
     *
     * @return array{
     *     artikel: array{terbit: int, draf: int, kategori: int},
     *     portofolio: array{jumlah: int, kategori: int},
     *     tutorial: array{terbit: int, draf: int, materi: int},
     *     layanan: int,
     *     faq: int,
     *     hero: array{jumlah: int, aktif: int},
     * }
     */
    public function statistik(): array
    {
        return [
            'artikel' => [
                'terbit' => Artikel::query()->terbit()->count(),
                'draf' => Artikel::query()->where('status_publikasi', StatusPublikasi::Draf)->count(),
                'kategori' => KategoriArtikel::query()->count(),
            ],
            'portofolio' => [
                'jumlah' => Portofolio::query()->count(),
                'kategori' => KategoriPortofolio::query()->count(),
            ],
            'tutorial' => [
                'terbit' => Tutorial::query()->where('status_publikasi', StatusPublikasi::Terbit)->count(),
                'draf' => Tutorial::query()->where('status_publikasi', StatusPublikasi::Draf)->count(),
                'materi' => MateriTutorial::query()->count(),
            ],
            'layanan' => Layanan::query()->count(),
            'faq' => Faq::query()->count(),
            'hero' => [
                'jumlah' => Hero::query()->count(),
                'aktif' => Hero::query()->aktif()->count(),
            ],
        ];
    }

    /**
     * Konten yang terakhir disimpan, dari kolom updated_at. Bukan riwayat: tiap konten muncul sekali dengan waktu simpan
     * terakhirnya, tanpa pelaku dan tanpa data yang sudah dihapus.
     *
     * @return Collection<int, array{modul: string, judul: string, diperbarui: CarbonInterface, url: string}>
     */
    public function terakhirDiperbarui(int $jumlah = 5): Collection
    {
        return collect(array_merge(
            $this->terbaruDari('Artikel', Artikel::query(), 'judul', 'admin.artikel.edit', $jumlah),
            $this->terbaruDari('Portofolio', Portofolio::query(), 'judul', 'admin.portofolio.edit', $jumlah),
            $this->terbaruDari('Tutorial', Tutorial::query(), 'judul', 'admin.tutorial.edit', $jumlah),
            $this->terbaruDari('Materi Tutorial', MateriTutorial::query(), 'judul', 'admin.materi-tutorial.edit', $jumlah),
            $this->terbaruDari('Layanan', Layanan::query(), 'judul', 'admin.layanan.edit', $jumlah),
            $this->terbaruDari('FAQ', Faq::query(), 'pertanyaan', 'admin.faq.edit', $jumlah),
            $this->terbaruDari('Hero', Hero::query(), 'judul', 'admin.hero.edit', $jumlah),
        ))
            // Pengurutan stabil: waktu kembar (mis. data seeder) tetap urut modul seperti di atas.
            ->sortByDesc(fn (array $item): int => $item['diperbarui']->getTimestamp())
            ->take($jumlah)
            ->values();
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return list<array{modul: string, judul: string, diperbarui: CarbonInterface, url: string}>
     */
    private function terbaruDari(string $modul, Builder $query, string $kolomJudul, string $rute, int $jumlah): array
    {
        $kunci = $query->getModel()->getKeyName();
        $hasil = [];

        foreach ($query->latest('updated_at')->latest($kunci)->limit($jumlah)->get([$kunci, $kolomJudul, 'updated_at']) as $item) {
            $diperbarui = $item->getAttribute('updated_at');

            // Kosong bila baris diisi di luar Eloquent; tanpa waktu simpan, baris itu tidak bisa diurutkan.
            if (! $diperbarui instanceof CarbonInterface) {
                continue;
            }

            $hasil[] = [
                'modul' => $modul,
                'judul' => (string) $item->getAttribute($kolomJudul),
                'diperbarui' => $diperbarui,
                'url' => route($rute, $item),
            ];
        }

        return $hasil;
    }
}
