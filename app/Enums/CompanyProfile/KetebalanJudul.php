<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Ketebalan judul besar frontend (judul hero, judul seksi, band ajakan, halaman error) lewat token
 * --ketebalan-judul. Judul kartu tetap semibold. Setiap font template dibangun sampai bobot 800.
 */
enum KetebalanJudul: string
{
    use MenyediakanOpsi;

    case Semibold = 'semibold';
    case Bold = 'bold';
    case Extrabold = 'extrabold';

    public function label(): string
    {
        return match ($this) {
            self::Semibold => 'Semibold (600)',
            self::Bold => 'Bold (700)',
            self::Extrabold => 'Extrabold (800)',
        };
    }

    public function nilai(): string
    {
        return match ($this) {
            self::Semibold => '600',
            self::Bold => '700',
            self::Extrabold => '800',
        };
    }
}
