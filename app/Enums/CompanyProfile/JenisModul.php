<?php

namespace App\Enums\CompanyProfile;

/**
 * Modul website yang diatur Pengaturan Sistem › Manajemen Modul: tampil atau tidaknya serta urutannya di menu navbar,
 * Tautan Cepat footer, dan seksi beranda. Halaman modul (artikel, portofolio, tutorial) tidak ikut diatur. Urutan case
 * = urutan bawaan (ModulWebsiteSeeder), yaitu urutan seksi beranda (Ajakan tepat di bawah Portofolio); Tutorial yang
 * hanya punya menu berada sebelum Artikel agar menunya tetap di antara Portofolio dan Artikel. Hero tidak termasuk
 * karena selalu menjadi seksi pertama dan memuat judul utama beranda (h1).
 */
enum JenisModul: string
{
    case Layanan = 'layanan';
    case Portofolio = 'portofolio';
    case Ajakan = 'ajakan';
    case Tutorial = 'tutorial';
    case Artikel = 'artikel';
    case Faq = 'faq';

    public function label(): string
    {
        return match ($this) {
            self::Layanan => 'Layanan',
            self::Portofolio => 'Portofolio',
            self::Ajakan => 'Ajakan',
            self::Tutorial => 'Tutorial',
            self::Artikel => 'Artikel',
            self::Faq => 'FAQ',
        };
    }

    /** Ajakan hanya berupa seksi beranda, tanpa menu. */
    public function punyaMenu(): bool
    {
        return $this !== self::Ajakan;
    }

    /** Tutorial hanya berupa menu ke halamannya, tanpa seksi beranda. */
    public function punyaSeksi(): bool
    {
        return $this !== self::Tutorial;
    }

    /**
     * Bagian website yang diatur modul ini.
     *
     * @return list<string>
     */
    public function tempatTampil(): array
    {
        return array_values(array_filter([
            $this->punyaMenu() ? 'Menu navbar & Tautan Cepat footer' : null,
            match ($this) {
                self::Tutorial => null,
                self::Ajakan => 'Seksi “Mulai bersama kami” di beranda',
                default => 'Seksi '.$this->label().' di beranda',
            },
        ]));
    }

    /**
     * Penjelasan di admin, ditulis setelah daftar tempatTampil().
     */
    public function akibatNonaktif(): string
    {
        return 'Bila nonaktif, bagian di atas disembunyikan dari website.'.match ($this) {
            self::Layanan => ' Kolom Layanan di footer dan kotak Layanan kami tetap tampil tanpa tautan. Isinya tetap bisa dikelola di menu Layanan.',
            self::Portofolio, self::Tutorial, self::Artikel => ' Halaman '.strtolower($this->label()).' tetap bisa dibuka lewat alamatnya. Isinya tetap bisa dikelola di menu '.$this->label().'.',
            self::Faq => ' Isinya tetap bisa dikelola di menu FAQ.',
            // Teks ajakan tertulis di kode, bukan isian admin.
            self::Ajakan => ' Band yang sama di halaman artikel, portofolio, dan tutorial tetap tampil.',
        };
    }

    /** Urutan bawaan: posisi case, mulai 1. */
    public function urutanBawaan(): int
    {
        return (int) array_search($this, self::cases(), true) + 1;
    }
}
