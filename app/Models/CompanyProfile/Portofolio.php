<?php

namespace App\Models\CompanyProfile;

use App\Models\Concerns\MencatatAktivitas;
use Database\Factories\CompanyProfile\PortofolioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Portofolio extends Model
{
    /** @use HasFactory<PortofolioFactory> */
    use HasFactory, MencatatAktivitas, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/portofolio';

    public const LABEL_LOG = 'Portofolio';

    public const ISIAN_LOG = [
        'judul' => 'Judul',
        'id_kategori_portofolio' => 'Kategori',
        'deskripsi' => 'Deskripsi',
        'gambar' => 'Gambar Utama',
        // Bukan kolom (tb_gambar_portofolio): ditandai PortofolioService lewat tandaiBerubah().
        'galeri' => 'Gambar Tambahan',
    ];

    protected $table = 'tb_portofolio';

    protected $primaryKey = 'id_portofolio';

    protected $fillable = ['id_kategori_portofolio', 'judul', 'slug', 'deskripsi', 'gambar'];

    public function namaUntukLog(): string
    {
        return $this->judul;
    }

    /**
     * Terbaru lebih dulu (id memecah created_at kembar, mis. data seeder yang dibuat dalam detik yang sama).
     *
     * @param  Builder<Portofolio>  $query
     */
    public function scopeTerbaru(Builder $query): void
    {
        $query->latest()->latest('id_portofolio');
    }

    /**
     * Portofolio untuk kartu & daftar frontend: terbaru lebih dulu beserta nama kategorinya (pita kategori).
     *
     * @param  Builder<Portofolio>  $query
     */
    public function scopeUntukKartu(Builder $query): void
    {
        $query->terbaru()->with('kategoriPortofolio:id_kategori_portofolio,nama_kategori');
    }

    /**
     * @return BelongsTo<KategoriPortofolio, $this>
     */
    public function kategoriPortofolio(): BelongsTo
    {
        return $this->belongsTo(KategoriPortofolio::class, 'id_kategori_portofolio', 'id_kategori_portofolio');
    }

    /**
     * Gambar tambahan untuk carousel detail, urut waktu unggah (tanpa kolom urutan).
     *
     * @return HasMany<GambarPortofolio, $this>
     */
    public function gambarPortofolio(): HasMany
    {
        return $this->hasMany(GambarPortofolio::class, 'id_portofolio', 'id_portofolio')->orderBy('id_gambar_portofolio');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
