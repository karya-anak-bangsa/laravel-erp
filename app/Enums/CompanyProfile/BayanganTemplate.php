<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Kekuatan bayangan permukaan frontend: tombol, isian, kartu, navbar, dan bingkai gambar hero (Full Color juga kotak
 * ajakan). Elemen melayang — menu ponsel, modal layanan, tombol WhatsApp, panah carousel — dan penanda
 * tema terpilih tidak ikut, karena bayangannya memisahkan lapisan atau menandai pilihan (pilihan pemilik 2026-10-14).
 */
enum BayanganTemplate: string
{
    use MenyediakanOpsi;

    case Tanpa = 'tanpa';
    case Halus = 'halus';
    case Tegas = 'tegas';

    // Bayangan nol transparan, bukan `none`, agar tetap sah di dalam daftar --tw-shadow (elemen ber-ring-1).
    private const NOL = '0 0 #0000';

    // Nilai shadow-2xs/xs/sm/md Tailwind v4 seperti yang ditulis build CSS (dipakai Monochrome dan Basecoat).
    private const TW_2XS = '0 1px #0000000d';

    private const TW_XS = '0 1px 2px 0 #0000000d';

    private const TW_SM = '0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a';

    private const TW_MD = '0 4px 6px -1px #0000001a, 0 2px 4px -2px #0000001a';

    public function label(): string
    {
        return match ($this) {
            self::Tanpa => 'Tanpa bayangan',
            self::Halus => 'Halus',
            self::Tegas => 'Tegas',
        };
    }

    /**
     * Token --bayangan-* satu jenis template (Monochrome ditambah --garis-kartu); nama tokennya sama di setiap tingkat.
     * Halus = tampilan yang disetujui pemilik 2026-10-11; Tegas = ±2× Halus di Full Color dan gaya shadcn klasik di
     * Monochrome; Tanpa = datar, dengan garis kartu Monochrome dipertegas agar kartu tetap jelas.
     *
     * @return array<string, string>
     */
    public function token(JenisTemplate $jenis): array
    {
        $nilai = match ($jenis) {
            JenisTemplate::FullColor => $this->nilaiFullColor(),
            JenisTemplate::Monochrome => $this->nilaiMonochrome(),
        };

        $token = [];

        foreach ($nilai as $nama => $bayangan) {
            // Tanpa: nama token sama dengan Halus, nilainya nol.
            $token["--bayangan-{$nama}"] = $this === self::Tanpa ? self::NOL : $bayangan;
        }

        if ($jenis === JenisTemplate::Monochrome) {
            // Tanpa bantuan bayangan, garis kartu satu tingkat lebih gelap (pilihan pemilik 2026-10-14).
            $token['--garis-kartu'] = $this === self::Tanpa ? 'var(--nada-300)' : 'color-mix(in oklab, var(--nada-200), var(--nada-300))';
        }

        return $token;
    }

    /**
     * Nilai Halus, atau Tegas bila dipilih.
     *
     * @return array<string, string>
     */
    private function nilaiFullColor(): array
    {
        $tegas = $this === self::Tegas;
        $kartu = $tegas
            ? '0 1px 2px '.self::slate(.05).', 0 10px 24px -12px '.self::slate(.16)
            : '0 1px 2px '.self::slate(.03).', 0 6px 16px -10px '.self::slate(.07);

        return [
            'tombol' => $tegas
                ? '0 1px 2px '.self::slate(.08).', 0 4px 10px -4px '.self::slate(.20)
                : '0 1px 2px '.self::slate(.06).', 0 2px 6px -3px '.self::slate(.10),
            'tombol-garis' => $tegas ? '0 1px 2px '.self::slate(.06).', 0 2px 5px -2px '.self::slate(.06) : self::TW_XS,
            // Isian dan lencana tidak dipertegas: bayangan kuat membuat isian tampak seperti tombol.
            'isian' => self::TW_XS,
            'lencana' => self::TW_2XS,
            'kartu' => $kartu,
            'kartu-sorot' => $tegas
                ? '0 1px 2px '.self::slate(.05).', 0 18px 36px -16px '.self::slate(.22)
                : '0 1px 2px '.self::slate(.03).', 0 12px 24px -14px '.self::slate(.11),
            'formulir' => $kartu,
            'gambar' => $tegas
                ? '0 1px 2px '.self::slate(.05).', 0 18px 44px -20px '.self::slate(.28)
                : '0 1px 2px '.self::slate(.03).', 0 10px 28px -16px '.self::slate(.14),
            'navbar' => $tegas
                ? '0 1px 0 '.self::slate(.04).', 0 8px 24px -12px '.self::slate(.14)
                : '0 1px 0 '.self::slate(.03).', 0 6px 18px -12px '.self::slate(.08),
            'ajakan' => $tegas
                ? '0 1px 2px '.self::slate(.06).', 0 28px 56px -28px color-mix(in oklab, var(--utama-teks) 34%, transparent)'
                : '0 1px 2px '.self::slate(.04).', 0 18px 36px -24px color-mix(in oklab, var(--utama-teks) 16%, transparent)',
        ];
    }

    /**
     * Nilai Halus, atau Tegas bila dipilih.
     *
     * @return array<string, string>
     */
    private function nilaiMonochrome(): array
    {
        $tegas = $this === self::Tegas;

        return [
            'tombol' => $tegas ? self::TW_SM : self::NOL,
            'tombol-garis' => self::TW_XS,
            'isian' => self::TW_XS,
            'lencana' => self::TW_XS,
            'kartu' => $tegas ? self::TW_SM : self::TW_XS,
            // Sorot kartu layanan satu tingkat di atas kartu (susunan sama dengan Full Color sejak 2026-10-19).
            'kartu-sorot' => $tegas ? self::TW_MD : self::TW_SM,
            'formulir' => $tegas ? self::TW_MD : self::TW_SM,
            'gambar' => $tegas ? self::TW_MD : self::TW_SM,
            'navbar' => $tegas ? self::TW_SM : self::TW_XS,
        ];
    }

    /**
     * Slate-900 beralfa, ditulis hex dengan alfa dibulatkan ke 1/255 — sama dengan cara build CSS menulis
     * rgb(15 23 42 / …) dulu, sehingga Halus identik dengan tampilan sebelum token ini pindah ke server.
     */
    private static function slate(float $alfa): string
    {
        return sprintf('#0f172a%02x', (int) round($alfa * 255));
    }
}
