<?php

namespace App\Models\Kas;

use App\Enums\Kas\JenisAkunKas;
use Database\Factories\Kas\AkunKasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
