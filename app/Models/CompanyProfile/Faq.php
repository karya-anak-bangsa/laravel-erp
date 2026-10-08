<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory, SoftDeletes;

    public const URUTAN_MAKS = 999;

    protected $table = 'tb_faq';

    protected $primaryKey = 'id_faq';

    protected $fillable = ['pertanyaan', 'jawaban', 'urutan_ke'];

    protected function casts(): array
    {
        return [
            'urutan_ke' => 'integer',
        ];
    }

    /**
     * Urutan tampil (admin & frontend): angka kecil lebih dulu; angka sama diurutkan pertanyaan.
     *
     * @param  Builder<Faq>  $query
     */
    public function scopeBerurutan(Builder $query): void
    {
        $query->orderBy('urutan_ke')->orderBy('pertanyaan');
    }

    /**
     * Urutan bawaan untuk FAQ baru: setelah FAQ terakhir.
     */
    public static function urutanBerikutnya(): int
    {
        return min((int) static::query()->max('urutan_ke') + 1, self::URUTAN_MAKS);
    }
}
