<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use Database\Factories\CompanyProfile\ArtikelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Artikel extends Model
{
    /** @use HasFactory<ArtikelFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/artikel';

    protected $table = 'tb_artikel';

    protected $primaryKey = 'id_artikel';

    protected $fillable = ['id_kategori_artikel', 'judul', 'slug', 'deskripsi', 'gambar', 'tanggal', 'status_publikasi'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status_publikasi' => StatusPublikasi::class,
        ];
    }

    /**
     * Hanya artikel terbit yang tampil di frontend.
     *
     * @param  Builder<Artikel>  $query
     */
    public function scopeTerbit(Builder $query): void
    {
        $query->where('status_publikasi', StatusPublikasi::Terbit);
    }

    /**
     * @return BelongsTo<KategoriArtikel, $this>
     */
    public function kategoriArtikel(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'id_kategori_artikel', 'id_kategori_artikel');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
