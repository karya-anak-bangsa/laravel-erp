<?php

namespace App\Enums\CompanyProfile;

/**
 * Modul website publik yang bisa ditampilkan atau disembunyikan lewat Pengaturan Sistem › Manajemen Modul. Urutan
 * case = urutan di Manajemen Modul dan di menu navbar. Hero tidak termasuk karena memuat judul utama beranda (h1).
 */
enum JenisModul: string
{
    case Layanan = 'layanan';
    case Portofolio = 'portofolio';
    case Tutorial = 'tutorial';
    case Artikel = 'artikel';
    case Faq = 'faq';
    case Ajakan = 'ajakan';

    public function label(): string
    {
        return match ($this) {
            self::Layanan => 'Layanan',
            self::Portofolio => 'Portofolio',
            self::Tutorial => 'Tutorial',
            self::Artikel => 'Artikel',
            self::Faq => 'FAQ',
            self::Ajakan => 'Ajakan',
        };
    }

    /**
     * Bagian website yang menampilkan modul ini; semuanya disembunyikan saat modul nonaktif.
     *
     * @return list<string>
     */
    public function tempatTampil(): array
    {
        $menu = 'Menu navbar & Tautan Cepat footer';

        return match ($this) {
            self::Layanan => [$menu, 'Seksi Layanan di beranda', 'Kolom Layanan di footer', 'Kotak Layanan kami di kolom samping halaman artikel, portofolio, dan tutorial'],
            self::Portofolio => [$menu, 'Seksi Portofolio di beranda', 'Halaman daftar & detail portofolio'],
            self::Tutorial => [$menu, 'Halaman daftar, kategori, tutorial, & materi'],
            self::Artikel => [$menu, 'Seksi Artikel di beranda', 'Halaman daftar, kategori, & detail artikel'],
            self::Faq => [$menu, 'Seksi FAQ di beranda'],
            self::Ajakan => ['Seksi “Mulai bersama kami” di beranda', 'Band yang sama di halaman artikel, portofolio, dan tutorial'],
        };
    }

    /**
     * Modul yang punya halaman sendiri: halamannya menjadi 404 dan keluar dari sitemap saat modul nonaktif.
     */
    public function punyaHalaman(): bool
    {
        return in_array($this, [self::Portofolio, self::Tutorial, self::Artikel], true);
    }

    /**
     * Penjelasan di admin, ditulis setelah daftar tempatTampil().
     */
    public function akibatNonaktif(): string
    {
        $akibat = 'Bila nonaktif, semua bagian di atas disembunyikan dari website.'
            .($this->punyaHalaman() ? ' Halamannya menjadi 404 dan keluar dari sitemap.' : '');

        // Teks ajakan tertulis di kode, bukan isian admin.
        return $this === self::Ajakan ? $akibat : $akibat.' Isinya tetap bisa dikelola di menu '.$this->label().'.';
    }
}
