<?php

namespace App\Models\Kas;

use App\Enums\Kas\JenisAkunKas;
use Database\Factories\Kas\AkunKasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_akun_kas
 * @property string $nama_akun
 * @property JenisAkunKas $jenis_akun
 * @property string|null $nama_bank
 * @property string|null $nomor_rekening
 * @property string $saldo_awal
 * @property Carbon $tanggal_saldo_awal
 * @property bool $status_aktif
 * @property string|null $keterangan
 * @property-read string|null $saldo hanya terisi bila query memakai SaldoKasService::denganSaldo()
 */
class AkunKas extends Model
{
    /** @use HasFactory<AkunKasFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_akun_kas';

    protected $primaryKey = 'id_akun_kas';

    protected $fillable = [
        'nama_akun',
        'jenis_akun',
        'nama_bank',
        'nomor_rekening',
        'saldo_awal',
        'tanggal_saldo_awal',
        'status_aktif',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jenis_akun' => JenisAkunKas::class,
            'saldo_awal' => 'decimal:2',
            'tanggal_saldo_awal' => 'date',
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TransaksiKas, $this>
     */
    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class, 'id_akun_kas', 'id_akun_kas');
    }
}
