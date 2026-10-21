<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\JenisModul;
use App\Models\Concerns\MencatatAktivitas;
use Database\Factories\CompanyProfile\ModulWebsiteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModulWebsite extends Model
{
    /** @use HasFactory<ModulWebsiteFactory> */
    use HasFactory, MencatatAktivitas, SoftDeletes;

    // Kunci cache status & urutan modul untuk frontend publik (ModulWebsiteService).
    public const KUNCI_CACHE = 'company-profile.modul-website';

    public const URUTAN_MAKS = 999;

    public const LABEL_LOG = 'Modul Website';

    public const ISIAN_LOG = [
        'status_aktif' => 'Status',
        'urutan_ke' => 'Urutan ke',
    ];

    protected $table = 'tb_modul_website';

    protected $primaryKey = 'id_modul_website';

    protected $fillable = ['jenis', 'urutan_ke', 'status_aktif'];

    protected function casts(): array
    {
        return [
            'jenis' => JenisModul::class,
            'urutan_ke' => 'integer',
            'status_aktif' => 'boolean',
        ];
    }

    public function namaUntukLog(): string
    {
        return $this->jenis->label();
    }

    protected static function booted(): void
    {
        // Sama dengan Identitas: perubahan langsung berlaku di website, dibuang setelah commit.
        $lupakanCache = fn () => DB::afterCommit(fn () => Cache::forget(self::KUNCI_CACHE));

        static::saved($lupakanCache);
        static::deleted($lupakanCache);
        static::restored($lupakanCache);
    }

    /**
     * Urutan tampil; angka sama diurutkan menurut urutan bawaan (baris dibuat ModulWebsiteSeeder seurut JenisModul).
     *
     * @param  Builder<ModulWebsite>  $query
     */
    public function scopeBerurutan(Builder $query): void
    {
        $query->orderBy('urutan_ke')->orderBy('id_modul_website');
    }
}
