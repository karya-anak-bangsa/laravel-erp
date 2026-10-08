<?php

namespace App\Services\Shared;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Slug unik dari judul untuk URL frontend (portofolio, artikel, dst.).
 */
class SlugService
{
    // Disisakan ruang dari kolom VARCHAR(220) untuk akhiran angka (-2, -3, ...).
    public const PANJANG_DASAR_MAKS = 200;

    /**
     * Slug yang sudah dipakai diberi akhiran angka. $query sebaiknya withTrashed() agar
     * slug data terhapus tidak bentrok saat kelak dipulihkan.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query  data pembanding
     * @param  TModel|null  $kecuali  data yang sedang diubah (slug miliknya sendiri boleh dipakai ulang)
     * @param  string  $cadangan  slug bila judul tidak menghasilkan huruf/angka sama sekali
     */
    public function buat(string $judul, Builder $query, ?Model $kecuali = null, string $cadangan = 'data', string $kolom = 'slug'): string
    {
        $dasar = rtrim(Str::substr(Str::slug($judul), 0, self::PANJANG_DASAR_MAKS), '-');

        if ($dasar === '') {
            $dasar = $cadangan;
        }

        $slug = $dasar;

        for ($nomor = 2; $this->dipakai($query, $kolom, $slug, $kecuali); $nomor++) {
            $slug = "{$dasar}-{$nomor}";
        }

        return $slug;
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    private function dipakai(Builder $query, string $kolom, string $slug, ?Model $kecuali): bool
    {
        return (clone $query)
            ->where($kolom, $slug)
            ->when($kecuali !== null, fn (Builder $query) => $query->whereKeyNot($kecuali?->getKey()))
            ->exists();
    }
}
