<?php

namespace App\Enums\CompanyProfile;

/**
 * Jenis template frontend publik: satu markup, dua tema. Nilainya dipakai sebagai atribut
 * data-tema di <html> dan sebagai isi cookie 'tema' pilihan pengunjung.
 */
enum JenisTemplate: string
{
    case FullColor = 'full_color';
    case Monochrome = 'monochrome';

    public function label(): string
    {
        return match ($this) {
            self::FullColor => 'Full Color',
            self::Monochrome => 'Monochrome',
        };
    }

    /**
     * Nilai cookie dari pengunjung tidak dipercaya: selain nilai yang dikenal jatuh ke tema bawaan.
     */
    public static function dariCookie(mixed $nilai): self
    {
        return is_string($nilai) ? (self::tryFrom($nilai) ?? self::FullColor) : self::FullColor;
    }
}
