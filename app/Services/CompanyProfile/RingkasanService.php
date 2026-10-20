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
use App\Models\CompanyProfile\ModulWebsite;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Template;
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
    public function terakhirDiperbarui(int $jumlah = 8): Collection
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
     * Konten yang belum tampil di website: artikel draf, tutorial draf, dan tutorial terbit yang belum punya materi,
     * terakhir disimpan lebih dulu.
     *
     * @return Collection<int, array{modul: string, judul: string, status: string, diperbarui: CarbonInterface, url: string}>
     */
    public function belumTampil(int $jumlah = 5): Collection
    {
        $draf = StatusPublikasi::Draf;

        return collect(array_merge(
            $this->belumTampilDari('Artikel', Artikel::query()->where('status_publikasi', $draf), 'Draf', 'admin.artikel.edit', $jumlah),
            $this->belumTampilDari('Tutorial', Tutorial::query()->where('status_publikasi', $draf), 'Draf', 'admin.tutorial.edit', $jumlah),
            // Terbit tetapi belum tampil karena scope tayang juga mensyaratkan minimal satu materi.
            $this->belumTampilDari('Tutorial', Tutorial::query()->where('status_publikasi', StatusPublikasi::Terbit)->doesntHave('materiTutorial'),
                'Belum ada materi', 'admin.tutorial.edit', $jumlah),
        ))
            // Pengurutan stabil: waktu kembar tetap urut seperti di atas.
            ->sortByDesc(fn (array $item): int => $item['diperbarui']->getTimestamp())
            ->take($jumlah)
            ->values();
    }

    /**
     * Yang sedang tampil di website: hero aktif, template aktif per jenis, izin mesin pencari (config/seo.php), dan
     * jumlah modul yang dinonaktifkan di Manajemen Modul agar tidak terlupa.
     *
     * @return array{hero: Hero|null, template: Collection<string, Template>, indeks: bool, modulNonaktif: int}
     */
    public function statusSitus(): array
    {
        return [
            'hero' => Hero::query()->aktif()->first(['id_hero', 'judul']),
            'template' => Template::query()->aktif()->get(['id_template', 'nama', 'jenis'])
                ->keyBy(fn (Template $template): string => $template->jenis->value)
                ->toBase(),
            'indeks' => (bool) config('seo.indeks'),
            'modulNonaktif' => ModulWebsite::query()->where('status_aktif', false)->count(),
        ];
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

        return $query->latest('updated_at')->latest($kunci)->limit($jumlah)
            ->get([$kunci, $kolomJudul, 'updated_at'])
            ->map(fn (Model $item): array => [
                'modul' => $modul,
                'judul' => (string) $item->getAttribute($kolomJudul),
                'diperbarui' => $item->getAttribute('updated_at'),
                'url' => route($rute, $item),
            ])
            ->values()
            ->all();
    }

    /**
     * Baris terbaruDari() ditambah label status untuk kartu Belum tampil.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return list<array{modul: string, judul: string, status: string, diperbarui: CarbonInterface, url: string}>
     */
    private function belumTampilDari(string $modul, Builder $query, string $status, string $rute, int $jumlah): array
    {
        $hasil = [];

        foreach ($this->terbaruDari($modul, $query, 'judul', $rute, $jumlah) as $item) {
            $hasil[] = [...$item, 'status' => $status];
        }

        return $hasil;
    }
}
