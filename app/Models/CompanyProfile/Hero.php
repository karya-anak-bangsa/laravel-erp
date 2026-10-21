<?php

namespace App\Models\CompanyProfile;

use App\Models\Concerns\MencatatAktivitas;
use Database\Factories\CompanyProfile\HeroFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Hero extends Model
{
    /** @use HasFactory<HeroFactory> */
    use HasFactory, MencatatAktivitas, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/hero';

    public const LABEL_LOG = 'Hero';

    public const ISIAN_LOG = [
        'judul' => 'Judul',
        'deskripsi' => 'Deskripsi',
        'keyword' => 'Keyword',
        'cta' => 'Tombol CTA',
        'gambar' => 'Gambar',
        'status_aktif' => 'Status',
    ];

    protected $table = 'tb_hero';

    protected $primaryKey = 'id_hero';

    protected $fillable = ['judul', 'deskripsi', 'gambar', 'keyword', 'cta', 'status_aktif'];

    protected function casts(): array
    {
        return [
            'keyword' => 'array',
            'cta' => 'array',
            'status_aktif' => 'boolean',
        ];
    }

    public function namaUntukLog(): string
    {
        return $this->judul;
    }

    /**
     * Hero yang tampil di frontend (hanya satu yang aktif, dijaga HeroService).
     *
     * @param  Builder<Hero>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status_aktif', true);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
