<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KategoriArtikelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriArtikel extends Model
{
    /** @use HasFactory<KategoriArtikelFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kategori_artikel';

    protected $primaryKey = 'id_kategori_artikel';

    protected $fillable = ['nama_kategori', 'slug'];

    /**
     * Kategori yang punya artikel terbit; hanya kategori ini yang punya halaman di frontend.
     *
     * @param  Builder<KategoriArtikel>  $query
     */
    public function scopeBerartikelTerbit(Builder $query): void
    {
        $query->has('artikelTerbit');
    }

    /**
     * Kotak Kategori di kolom samping halaman artikel: urut abjad, beserta jumlah artikel terbitnya
     * (artikel_terbit_count).
     *
     * @return Collection<int, static>
     */
    public static function untukSamping(): Collection
    {
        return static::query()
            ->select(['id_kategori_artikel', 'nama_kategori', 'slug'])
            ->withCount('artikelTerbit')
            ->berartikelTerbit()
            ->orderBy('nama_kategori')
            ->get();
    }

    /**
     * Slug dikunci begitu halaman kategori tayang, yaitu selama kategori punya artikel terbit.
     */
    public function slugTerkunci(): bool
    {
        return $this->artikelTerbit()->exists();
    }

    /**
     * @return HasMany<Artikel, $this>
     */
    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class, 'id_kategori_artikel', 'id_kategori_artikel');
    }

    /**
     * Artikel yang tampil di frontend: dasar halaman kategori, jumlah di kotak Kategori, dan kunci slug.
     *
     * @return HasMany<Artikel, $this>
     */
    public function artikelTerbit(): HasMany
    {
        return $this->hasMany(Artikel::class, 'id_kategori_artikel', 'id_kategori_artikel')->terbit();
    }
}
