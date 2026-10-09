<?php

namespace App\Enums\CompanyProfile;

/**
 * Skala abu-abu tema Monochrome (pilihan "base color" ala shadcn, nilai oklch Tailwind v4).
 */
enum NadaDasar: string
{
    case Netral = 'netral';
    case Zinc = 'zinc';
    case Stone = 'stone';
    case Slate = 'slate';

    /** Tingkat skala yang menjadi nama token --nada-{tingkat}. */
    public const TINGKAT = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

    public function label(): string
    {
        return match ($this) {
            self::Netral => 'Netral',
            self::Zinc => 'Zinc (abu kebiruan)',
            self::Stone => 'Stone (abu hangat)',
            self::Slate => 'Slate (abu sejuk)',
        };
    }

    /**
     * @return list<string> 11 nilai oklch() sesuai urutan TINGKAT
     */
    public function skala(): array
    {
        $nilai = match ($this) {
            self::Netral => ['98.5% 0 0', '97% 0 0', '92.2% 0 0', '87% 0 0', '70.8% 0 0', '55.6% 0 0', '43.9% 0 0', '37.1% 0 0', '26.9% 0 0', '20.5% 0 0', '14.5% 0 0'],
            self::Zinc => ['98.5% 0 0', '96.7% 0.001 286.375', '92% 0.004 286.32', '87.1% 0.006 286.286', '70.5% 0.015 286.067', '55.2% 0.016 285.938', '44.2% 0.017 285.786', '37% 0.013 285.805', '27.4% 0.006 286.033', '21% 0.006 285.885', '14.1% 0.005 285.823'],
            self::Stone => ['98.5% 0.001 106.423', '97% 0.001 106.424', '92.3% 0.003 48.717', '86.9% 0.005 56.366', '70.9% 0.01 56.259', '55.3% 0.013 58.071', '44.4% 0.011 73.639', '37.4% 0.01 67.558', '26.8% 0.007 34.298', '21.6% 0.006 56.043', '14.7% 0.004 49.25'],
            self::Slate => ['98.4% 0.003 247.858', '96.8% 0.007 247.896', '92.9% 0.013 255.508', '86.9% 0.022 252.894', '70.4% 0.04 256.788', '55.4% 0.046 257.417', '44.6% 0.043 257.281', '37.2% 0.044 257.287', '27.9% 0.041 260.031', '20.8% 0.042 265.755', '12.9% 0.042 264.695'],
        };

        return array_map(fn (string $v): string => "oklch({$v})", $nilai);
    }
}
