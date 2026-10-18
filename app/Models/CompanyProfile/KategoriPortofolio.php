<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KategoriPortofolioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPortofolio extends Model
{
    /** @use HasFactory<KategoriPortofolioFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kategori_portofolio';

    protected $primaryKey = 'id_kategori_portofolio';

    protected $fillable = ['nama_kategori'];

    /**
     * Pilihan kategori untuk form dan filter portofolio.
     *
     * @return array<int, string> [id_kategori_portofolio => nama_kategori], urut abjad
     */
    public static function opsi(): array
    {
        return static::query()->orderBy('nama_kategori')->pluck('nama_kategori', 'id_kategori_portofolio')->all();
    }

    /**
     * @return HasMany<Portofolio, $this>
     */
    public function portofolio(): HasMany
    {
        return $this->hasMany(Portofolio::class, 'id_kategori_portofolio', 'id_kategori_portofolio');
    }
}
