<?php

namespace App\Models;

use App\Enums\Sistem\PeranPengguna;
use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'tb_pengguna';

    protected $primaryKey = 'id_pengguna';

    // Tabel tidak punya kolom remember_token; string kosong membuat Laravel
    // tidak pernah membaca/menulisnya (termasuk saat logout).
    protected $rememberTokenName = '';

    protected $fillable = ['nama', 'email', 'password', 'peran'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'peran' => PeranPengguna::class,
        ];
    }
}
