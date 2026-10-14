<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Kekuatan bayangan tombol, kartu, dan gambar hero. Pilihan & efeknya dibuat di Fase 4 langkah 3b;
 * sampai itu setiap template memakai Halus (tampilan sekarang).
 */
enum BayanganTemplate: string
{
    use MenyediakanOpsi;

    case Tanpa = 'tanpa';
    case Halus = 'halus';
    case Tegas = 'tegas';

    public function label(): string
    {
        return match ($this) {
            self::Tanpa => 'Tanpa bayangan',
            self::Halus => 'Halus',
            self::Tegas => 'Tegas',
        };
    }
}
