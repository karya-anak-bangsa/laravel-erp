<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\IdentitasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Identitas extends Model
{
    /** @use HasFactory<IdentitasFactory> */
    use HasFactory, SoftDeletes;

    // Logo & favicon disimpan di disk public agar bisa ditampilkan di frontend.
    public const DISK = 'public';

    public const FOLDER = 'company-profile/identitas';

    // Kunci cache identitas untuk frontend publik (IdentitasService::untukSitus).
    public const KUNCI_CACHE = 'company-profile.identitas';

    protected $table = 'tb_identitas';

    protected $primaryKey = 'id_identitas';

    protected $fillable = [
        'nama_perusahaan',
        'judul_website',
        'alamat_website',
        'meta_deskripsi',
        'meta_keyword',
        'logo_website',
        'favicon_website',
        'email',
        'telepon',
        'alamat',
        'link_youtube',
        'link_instagram',
        'link_whatsapp',
    ];

    protected static function booted(): void
    {
        // Setiap perubahan (admin, seeder, kode lain) langsung tampil di frontend. Dibuang setelah commit
        // agar request lain tidak sempat mengisi ulang cache dengan data lama selama transaksi berjalan.
        $lupakanCache = fn () => DB::afterCommit(fn () => Cache::forget(self::KUNCI_CACHE));

        static::saved($lupakanCache);
        static::deleted($lupakanCache);
        static::restored($lupakanCache);
    }

    /**
     * Baris identitas satu-satunya (singleton, dibuat oleh IdentitasSeeder).
     */
    public static function tunggal(): self
    {
        return static::query()->oldest('id_identitas')->firstOrFail();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->logo_website));
    }

    /**
     * @return Attribute<string, never>
     */
    protected function faviconUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->favicon_website));
    }
}
