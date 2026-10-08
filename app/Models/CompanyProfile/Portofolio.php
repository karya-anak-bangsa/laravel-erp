<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\PortofolioFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Portofolio extends Model
{
    /** @use HasFactory<PortofolioFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/portofolio';

    protected $table = 'tb_portofolio';

    protected $primaryKey = 'id_portofolio';

    protected $fillable = ['judul', 'slug', 'deskripsi', 'gambar', 'kategori'];

    /**
     * Kategori yang sedang dipakai, untuk saran isian dan filter daftar.
     *
     * @return list<string>
     */
    public static function daftarKategori(): array
    {
        return static::query()->distinct()->orderBy('kategori')->pluck('kategori')->all();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
