<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\KontakKamiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontakKami extends Model
{
    /** @use HasFactory<KontakKamiFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tb_kontak_kami';

    protected $primaryKey = 'id_kontak_kami';

    protected $fillable = ['nama', 'email', 'subjek', 'pesan', 'tanggal', 'status_baca'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'status_baca' => 'boolean',
        ];
    }

    /**
     * @param  Builder<KontakKami>  $query
     */
    public function scopeBelumDibaca(Builder $query): void
    {
        $query->where('status_baca', false);
    }
}
