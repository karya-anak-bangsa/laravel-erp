<?php

namespace App\Enums\CompanyProfile;

/**
 * Lebar maksimum isi halaman frontend di monitor ≥1920px (24" ke atas). Di bawahnya selalu
 * 80rem (1280px) agar laptop 1536px tetap bermargin cukup.
 */
enum LebarKonten: string
{
    case Standar = 'standar';
    case Lebar = 'lebar';

    public const DASAR = '80rem';

    public function label(): string
    {
        return match ($this) {
            self::Standar => 'Standar (1280px)',
            self::Lebar => 'Lebar (1440px)',
        };
    }

    public function nilai(): string
    {
        return match ($this) {
            self::Standar => self::DASAR,
            self::Lebar => '90rem',
        };
    }
}
