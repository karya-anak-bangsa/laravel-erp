<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Program perusahaan yang dicontohkan sebuah tutorial; urutannya mengikuti urutan layanan.
 */
enum JenisProgram: string
{
    use MenyediakanOpsi;

    case PelatihanIt = 'pelatihan_it';
    case SertifikasiIt = 'sertifikasi_it';
    case Bootcamp = 'bootcamp';

    public function label(): string
    {
        return match ($this) {
            self::PelatihanIt => 'Pelatihan IT',
            self::SertifikasiIt => 'Sertifikasi IT',
            self::Bootcamp => 'Bootcamp',
        };
    }
}
