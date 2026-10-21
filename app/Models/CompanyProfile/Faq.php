<?php

namespace App\Models\CompanyProfile;

use App\Models\Concerns\MencatatAktivitas;
use Database\Factories\CompanyProfile\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory, MencatatAktivitas, SoftDeletes;

    public const URUTAN_MAKS = 999;

    public const LABEL_LOG = 'FAQ';

    public const ISIAN_LOG = [
        'pertanyaan' => 'Pertanyaan',
        'jawaban' => 'Jawaban',
        'urutan_ke' => 'Urutan ke',
    ];

    protected $table = 'tb_faq';

    protected $primaryKey = 'id_faq';

    protected $fillable = ['pertanyaan', 'jawaban', 'urutan_ke'];

    protected function casts(): array
    {
        return [
            'urutan_ke' => 'integer',
        ];
    }

    public function namaUntukLog(): string
    {
        return $this->pertanyaan;
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
