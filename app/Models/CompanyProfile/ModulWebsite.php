<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use Database\Factories\CompanyProfile\ModulWebsiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModulWebsite extends Model
{
    /** @use HasFactory<ModulWebsiteFactory> */
    use HasFactory, SoftDeletes;

    // Kunci cache status modul untuk frontend publik (ModulWebsiteService::statusTampil).
    public const KUNCI_CACHE = 'company-profile.modul-website';

    protected $table = 'tb_modul_website';

    protected $primaryKey = 'id_modul_website';

    protected $fillable = ['jenis', 'status_aktif'];

    protected function casts(): array
    {
        return [
            'jenis' => JenisModul::class,
            'status_aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Sama dengan Identitas: perubahan status langsung berlaku di website, dibuang setelah commit.
        $lupakanCache = fn () => DB::afterCommit(fn () => Cache::forget(self::KUNCI_CACHE));

        static::saved($lupakanCache);
        static::deleted($lupakanCache);
        static::restored($lupakanCache);
    }
}
