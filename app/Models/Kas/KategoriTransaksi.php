<?php

namespace App\Models\Kas;

use App\Enums\Kas\JenisTransaksi;
use Database\Factories\Kas\KategoriTransaksiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_kategori_transaksi
 * @property string $nama_kategori
 * @property JenisTransaksi $jenis_transaksi
 * @property string|null $keterangan
 */
class KategoriTransaksi extends Model
{
    /** @use HasFactory<KategoriTransaksiFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kategori_transaksi';

    protected $primaryKey = 'id_kategori_transaksi';

    protected $fillable = [
        'nama_kategori',
        'jenis_transaksi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jenis_transaksi' => JenisTransaksi::class,
        ];
    }

    /**
     * Pilihan kategori per jenis untuk <optgroup>: ['Pemasukan' => [id => nama], ...].
     *
     * @return array<string, array<int, string>>
     */
    public static function opsiPerJenis(?JenisTransaksi $hanya = null): array
    {
        return static::query()
            ->when($hanya, fn (Builder $query) => $query->where('jenis_transaksi', $hanya))
            ->orderBy('jenis_transaksi')
            ->orderBy('nama_kategori')
            ->get()
            ->groupBy(fn (self $kategori) => $kategori->jenis_transaksi->label())
            ->map(fn (Collection $grup) => $grup->pluck('nama_kategori', 'id_kategori_transaksi')->all())
            ->all();
    }

    /**
     * @return HasMany<TransaksiKas, $this>
     */
    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class, 'id_kategori_transaksi', 'id_kategori_transaksi');
    }
}
