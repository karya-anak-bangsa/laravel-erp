<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\HeroFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Hero extends Model
{
    /** @use HasFactory<HeroFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/hero';

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

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
