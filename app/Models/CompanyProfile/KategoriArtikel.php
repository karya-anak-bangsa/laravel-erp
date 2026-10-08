<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KategoriArtikelFactory;
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

    protected $fillable = ['nama_kategori'];

    /**
     * @return HasMany<Artikel, $this>
     */
    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class, 'id_kategori_artikel', 'id_kategori_artikel');
    }
}
