<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Font template frontend. Font di-host sendiri lewat vite.config.js; alias() = nama untuk @fonts.
 * Setiap font di sini wajib dibangun di vite.config.js (bobot 400–800, dijaga test) sebelum bisa dipilih
 * admin, karena @fonts dengan alias yang tidak ada di manifest menggagalkan halaman.
 */
enum FontTemplate: string
{
    use MenyediakanOpsi;

    case PlusJakartaSans = 'plus_jakarta_sans';
    case Geist = 'geist';
    case Inter = 'inter';
    case Poppins = 'poppins';
    case Manrope = 'manrope';
    case DmSans = 'dm_sans';

    public function family(): string
    {
        return match ($this) {
            self::PlusJakartaSans => 'Plus Jakarta Sans',
            self::Geist => 'Geist',
            self::Inter => 'Inter',
            self::Poppins => 'Poppins',
            self::Manrope => 'Manrope',
            self::DmSans => 'DM Sans',
        };
    }

    public function label(): string
    {
        return $this->family();
    }

    /**
     * Slug nama family, sama dengan alias bawaan laravel-vite-plugin.
     */
    public function alias(): string
    {
        return str_replace('_', '-', $this->value);
    }
}
