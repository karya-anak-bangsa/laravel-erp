<?php

namespace App\Models\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Pengguna;
use Database\Factories\Kas\TransaksiKasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_transaksi_kas
 * @property string $nomor_transaksi
 * @property int $id_akun_kas
 * @property int $id_kategori_transaksi
 * @property JenisTransaksi $jenis_transaksi
 * @property Carbon $tanggal_transaksi
 * @property string $jumlah
 * @property string|null $nama_pihak
 * @property string $keterangan
 * @property string|null $bukti_transaksi
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class TransaksiKas extends Model
{
    /** @use HasFactory<TransaksiKasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_transaksi_kas';

    protected $primaryKey = 'id_transaksi_kas';

    // nomor_transaksi, jenis_transaksi, bukti_transaksi, created_by, updated_by diisi
    // TransaksiKasService, bukan langsung dari input form.
    protected $fillable = [
        'nomor_transaksi',
        'id_akun_kas',
        'id_kategori_transaksi',
        'jenis_transaksi',
        'tanggal_transaksi',
        'jumlah',
        'nama_pihak',
        'keterangan',
        'bukti_transaksi',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis_transaksi' => JenisTransaksi::class,
            'tanggal_transaksi' => 'date',
            'jumlah' => 'decimal:2',
        ];
    }

    // withTrashed: transaksi lama tetap menampilkan akun/kategori/pengguna yang sudah dihapus.

    /**
     * @return BelongsTo<AkunKas, $this>
     */
    public function akunKas(): BelongsTo
    {
        return $this->belongsTo(AkunKas::class, 'id_akun_kas', 'id_akun_kas')->withTrashed();
    }

    /**
     * @return BelongsTo<KategoriTransaksi, $this>
     */
    public function kategoriTransaksi(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class, 'id_kategori_transaksi', 'id_kategori_transaksi')->withTrashed();
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'created_by', 'id_pengguna')->withTrashed();
    }

    /**
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'updated_by', 'id_pengguna')->withTrashed();
    }
}
