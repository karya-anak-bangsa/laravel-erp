<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KategoriTutorialFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriTutorial extends Model
{
    /** @use HasFactory<KategoriTutorialFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kategori_tutorial';

    protected $primaryKey = 'id_kategori_tutorial';

    protected $fillable = ['nama_kategori', 'slug'];

    /**
     * Pilihan kategori untuk form dan filter tutorial.
     *
     * @return array<int, string> [id_kategori_tutorial => nama_kategori], urut abjad
     */
    public static function opsi(): array
    {
        return static::query()->orderBy('nama_kategori')->pluck('nama_kategori', 'id_kategori_tutorial')->all();
    }

    /**
     * Kotak Kategori di kolom samping halaman tutorial: hanya kategori yang punya halaman (punya tutorial tayang), urut
     * abjad, beserta jumlah tutorial tayangnya (tutorial_tayang_count).
     *
     * @return Collection<int, static>
     */
    public static function untukSamping(): Collection
    {
        return static::query()
            ->select(['id_kategori_tutorial', 'nama_kategori', 'slug'])
            ->withCount('tutorialTayang')
            ->has('tutorialTayang')
            ->orderBy('nama_kategori')
            ->get();
    }

    /**
     * Slug dikunci begitu halaman kategori tayang, yaitu selama kategori punya tutorial tayang.
     */
    public function slugTerkunci(): bool
    {
        return $this->tutorialTayang()->exists();
    }

    /**
     * @return HasMany<Tutorial, $this>
     */
    public function tutorial(): HasMany
    {
        return $this->hasMany(Tutorial::class, 'id_kategori_tutorial', 'id_kategori_tutorial');
    }

    /**
     * Tutorial yang tampil di frontend: dasar halaman kategori, jumlah di kotak Kategori, dan kunci slug.
     *
     * @return HasMany<Tutorial, $this>
     */
    public function tutorialTayang(): HasMany
    {
        return $this->hasMany(Tutorial::class, 'id_kategori_tutorial', 'id_kategori_tutorial')->tayang();
    }
}
