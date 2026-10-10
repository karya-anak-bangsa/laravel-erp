<?php

namespace App\Enums\CompanyProfile;

/**
 * Ukuran elemen frontend di desktop (≥1024px): tinggi navbar, logo, teks menu, dan tombol besar.
 * Nilainya per jenis template karena gaya kedua tema berbeda; di bawah 1024px ukuran tetap
 * mengikuti CSS agar sasaran sentuh di ponsel tidak ikut mengecil.
 */
enum SkalaTemplate: string
{
    case Ringkas = 'ringkas';
    case Standar = 'standar';
    case Lega = 'lega';

    public function label(): string
    {
        return match ($this) {
            self::Ringkas => 'Ringkas',
            self::Standar => 'Standar',
            self::Lega => 'Lega',
        };
    }

    /**
     * Satu tingkat lebih besar, dipakai di layar ≥1920px (monitor 24" ke atas).
     */
    public function naik(): self
    {
        return match ($this) {
            self::Ringkas => self::Standar,
            self::Standar, self::Lega => self::Lega,
        };
    }

    /**
     * @return array{navbar: string, logo: string, menu: string, tombol: string, teks-tombol: string}
     */
    public function token(JenisTemplate $jenis): array
    {
        // Full Color Lega = ukuran awal Fase 4 langkah 2a; Monochrome Ringkas = ukuran awal Monochrome.
        return match ($jenis) {
            JenisTemplate::FullColor => match ($this) {
                self::Ringkas => ['navbar' => '4rem', 'logo' => '2.25rem', 'menu' => '.875rem', 'tombol' => '2.75rem', 'teks-tombol' => '.9375rem'],
                self::Standar => ['navbar' => '4.5rem', 'logo' => '2.375rem', 'menu' => '.9375rem', 'tombol' => '2.875rem', 'teks-tombol' => '.9375rem'],
                self::Lega => ['navbar' => '5rem', 'logo' => '2.5rem', 'menu' => '.9375rem', 'tombol' => '3rem', 'teks-tombol' => '1rem'],
            },
            JenisTemplate::Monochrome => match ($this) {
                self::Ringkas => ['navbar' => '4rem', 'logo' => '1.75rem', 'menu' => '.875rem', 'tombol' => '2.5rem', 'teks-tombol' => '.875rem'],
                self::Standar => ['navbar' => '4.25rem', 'logo' => '1.875rem', 'menu' => '.875rem', 'tombol' => '2.75rem', 'teks-tombol' => '.875rem'],
                self::Lega => ['navbar' => '4.5rem', 'logo' => '2rem', 'menu' => '.9375rem', 'tombol' => '3rem', 'teks-tombol' => '.9375rem'],
            },
        };
    }
}
