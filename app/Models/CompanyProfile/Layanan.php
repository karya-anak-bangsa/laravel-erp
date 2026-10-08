<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\LayananFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Layanan extends Model
{
    /** @use HasFactory<LayananFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/layanan';

    public const URUTAN_MAKS = 999;

    protected $table = 'tb_layanan';

    protected $primaryKey = 'id_layanan';

    protected $fillable = ['judul', 'deskripsi', 'gambar', 'keterangan', 'urutan_ke'];

    protected function casts(): array
    {
        return [
            'urutan_ke' => 'integer',
        ];
    }

    /**
     * Urutan tampil (admin & frontend): angka kecil lebih dulu; angka sama diurutkan judul.
     *
     * @param  Builder<Layanan>  $query
     */
    public function scopeBerurutan(Builder $query): void
    {
        $query->orderBy('urutan_ke')->orderBy('judul');
    }

    /**
     * Urutan bawaan untuk layanan baru: setelah layanan terakhir.
     */
    public static function urutanBerikutnya(): int
    {
        return min((int) static::query()->max('urutan_ke') + 1, self::URUTAN_MAKS);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
