<?php

namespace App\Models\Kas;

use App\Enums\Kas\JenisTransaksi;
use Database\Factories\Kas\KategoriTransaksiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
