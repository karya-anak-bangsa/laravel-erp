<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use Database\Factories\CompanyProfile\MateriTutorialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MateriTutorial extends Model
{
    /** @use HasFactory<MateriTutorialFactory> */
    use HasFactory, SoftDeletes;

    public const URUTAN_MAKS = 999;

    protected $table = 'tb_materi_tutorial';

    protected $primaryKey = 'id_materi_tutorial';

    protected $fillable = ['id_tutorial', 'judul', 'slug', 'deskripsi', 'urutan_ke'];

    protected function casts(): array
    {
        return [
            'urutan_ke' => 'integer',
        ];
    }

    /**
     * Urutan materi dalam tutorialnya: angka kecil lebih dulu; angka sama menurut urutan dibuat.
     *
     * @param  Builder<MateriTutorial>  $query
     */
    public function scopeBerurutan(Builder $query): void
    {
        $query->orderBy('urutan_ke')->orderBy('id_materi_tutorial');
    }

    /**
     * Urutan bawaan materi baru: setelah materi terakhir tutorialnya.
     */
    public static function urutanBerikutnya(int $idTutorial): int
    {
        return min((int) static::query()->where('id_tutorial', $idTutorial)->max('urutan_ke') + 1, self::URUTAN_MAKS);
    }

    /**
     * Alamat materi tayang begitu tutorialnya terbit, jadi sejak itu slug dikunci agar tautannya tidak rusak.
     */
    public function slugTerkunci(): bool
    {
        return $this->tutorial->status_publikasi === StatusPublikasi::Terbit;
    }

    /**
     * @return BelongsTo<Tutorial, $this>
     */
    public function tutorial(): BelongsTo
    {
        return $this->belongsTo(Tutorial::class, 'id_tutorial', 'id_tutorial');
    }
}
