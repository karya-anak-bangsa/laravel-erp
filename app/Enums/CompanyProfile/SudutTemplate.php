<?php

namespace App\Enums\CompanyProfile;

/**
 * Kelengkungan sudut komponen frontend; setiap pilihan menghasilkan lima token --sudut-*.
 */
enum SudutTemplate: string
{
    case Tajam = 'tajam';
    case Sedang = 'sedang';
    case Bulat = 'bulat';

    public function label(): string
    {
        return match ($this) {
            self::Tajam => 'Tajam',
            self::Sedang => 'Sedang',
            self::Bulat => 'Bulat',
        };
    }

    /**
     * @return array{kartu: string, dalam: string, tombol: string, lencana: string, isian: string}
     */
    public function token(): array
    {
        return match ($this) {
            self::Tajam => ['kartu' => '.375rem', 'dalam' => '.25rem', 'tombol' => '.25rem', 'lencana' => '.25rem', 'isian' => '.25rem'],
            self::Sedang => ['kartu' => '.75rem', 'dalam' => '.5rem', 'tombol' => '.5rem', 'lencana' => '.375rem', 'isian' => '.5rem'],
            self::Bulat => ['kartu' => '1.5rem', 'dalam' => '1rem', 'tombol' => '9999px', 'lencana' => '9999px', 'isian' => '.75rem'],
        };
    }
}
