<?php

namespace App\Enums\CompanyProfile;

/**
 * Font template frontend. Font di-host sendiri lewat vite.config.js; alias() = nama untuk @fonts.
 * Baru Plus Jakarta Sans & Geist (dua template bawaan) yang dibangun; font lain wajib ditambahkan
 * ke vite.config.js sebelum bisa dipilih admin (Fase 4 langkah 3).
 */
enum FontTemplate: string
{
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

    /**
     * Slug nama family, sama dengan alias bawaan laravel-vite-plugin.
     */
    public function alias(): string
    {
        return str_replace('_', '-', $this->value);
    }
}
