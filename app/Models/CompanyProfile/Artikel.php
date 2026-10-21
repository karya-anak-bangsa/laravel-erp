<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\Concerns\MencatatAktivitas;
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
    use HasFactory, MencatatAktivitas, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/artikel';

    public const LABEL_LOG = 'Artikel';

    public const ISIAN_LOG = [
        'judul' => 'Judul',
        'deskripsi' => 'Isi Artikel',
        'gambar' => 'Gambar',
        'id_kategori_artikel' => 'Kategori',
        'tanggal' => 'Tanggal',
        'status_publikasi' => 'Status',
    ];

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

    public function namaUntukLog(): string
    {
        return $this->judul;
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
     * Artikel terbit, terbaru lebih dulu menurut tanggal (id memecah tanggal kembar).
     *
     * @param  Builder<Artikel>  $query
     */
    public function scopeTerbaru(Builder $query): void
    {
        $query->terbit()->orderByDesc('tanggal')->orderByDesc('id_artikel');
    }

    /**
     * Artikel untuk kartu frontend (beranda, daftar artikel): artikel terbaru beserta nama kategorinya.
     *
     * @param  Builder<Artikel>  $query
     */
    public function scopeUntukKartu(Builder $query): void
    {
        $query->terbaru()->with('kategoriArtikel:id_kategori_artikel,nama_kategori');
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
