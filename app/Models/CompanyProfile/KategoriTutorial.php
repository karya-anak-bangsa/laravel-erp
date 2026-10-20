<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KategoriTutorialFactory;
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

    protected $fillable = ['nama_kategori'];

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
     * @return HasMany<Tutorial, $this>
     */
    public function tutorial(): HasMany
    {
        return $this->hasMany(Tutorial::class, 'id_kategori_tutorial', 'id_kategori_tutorial');
    }
}
