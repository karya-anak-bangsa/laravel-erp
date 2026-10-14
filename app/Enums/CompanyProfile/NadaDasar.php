<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Nada abu-abu tema Monochrome (pilihan "base color" ala shadcn), nilai oklch. Semua nada memakai kecerahan
 * Tailwind neutral agar kontras teks setara Netral; nada bertinta memakai kekuatan tinta Tailwind slate. Skala abu
 * Tailwind lain (zinc, stone, dst.) tidak dipakai karena bedanya dengan Netral nyaris tak terlihat (review pemilik
 * 2026-10-14).
 */
enum NadaDasar: string
{
    use MenyediakanOpsi;

    case Netral = 'netral';
    case Biru = 'biru';
    case Hijau = 'hijau';
    case Ungu = 'ungu';
    case Cokelat = 'cokelat';

    /** Tingkat skala yang menjadi nama token --nada-{tingkat}. */
    public const TINGKAT = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

    /** Kecerahan oklch per tingkat (Tailwind neutral). */
    private const KECERAHAN = ['98.5%', '97%', '92.2%', '87%', '70.8%', '55.6%', '43.9%', '37.1%', '26.9%', '20.5%', '14.5%'];

    /** Kekuatan tinta (kroma oklch) per tingkat (Tailwind slate), dipakai semua nada bertinta. */
    private const KROMA = ['0.003', '0.007', '0.013', '0.022', '0.04', '0.046', '0.043', '0.044', '0.041', '0.042', '0.042'];

    public function label(): string
    {
        return match ($this) {
            self::Netral => 'Netral (hitam-putih)',
            self::Biru => 'Biru (abu kebiruan)',
            self::Hijau => 'Hijau (abu kehijauan)',
            self::Ungu => 'Ungu (abu keunguan)',
            self::Cokelat => 'Cokelat (abu kecokelatan)',
        };
    }

    /**
     * @return list<string> 11 nilai oklch() sesuai urutan TINGKAT
     */
    public function skala(): array
    {
        $hue = match ($this) {
            self::Netral => null,
            self::Biru => 257,
            self::Hijau => 165,
            self::Ungu => 305,
            self::Cokelat => 55,
        };

        $skala = [];

        foreach (self::TINGKAT as $i => $tingkat) {
            // Tinta menaikkan luminans sebagian nada, jadi tingkat 500 (teks redup) digelapkan sedikit agar tetap
            // ≥ 4,5:1 di latar seksi redup, setara Netral.
            $kecerahan = $hue !== null && $tingkat === 500 ? '55%' : self::KECERAHAN[$i];
            $skala[] = $hue === null ? "oklch({$kecerahan} 0 0)" : "oklch({$kecerahan} ".self::KROMA[$i]." {$hue})";
        }

        return $skala;
    }
}
