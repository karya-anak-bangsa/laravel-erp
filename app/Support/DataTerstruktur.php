<?php

namespace App\Support;

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Tutorial;
use Illuminate\Support\Str;

/**
 * Data terstruktur schema.org (JSON-LD) untuk mesin pencari, dirender <x-web.data-terstruktur>. Organisasi & situs
 * hanya di beranda sesuai anjuran Google; halaman artikel memuat artikel dan remah rotinya, halaman daftar & kategori
 * artikel serta halaman portofolio dan tutorial cukup remah rotinya. FAQPage sengaja tidak dipakai: sejak Agustus 2023
 * Google hanya menampilkan hasil kaya FAQ untuk situs pemerintah dan kesehatan.
 */
class DataTerstruktur
{
    private const BAHASA = 'id-ID';

    /**
     * Organisasi (nama, logo, kontak) dan situs, agar Google mengenali nama situs dan logo perusahaan.
     *
     * @return array<string, mixed>
     */
    public static function beranda(Identitas $identitas): array
    {
        $akar = Seo::alamat();

        return self::dokumen([
            array_filter([
                '@type' => 'Organization',
                '@id' => $akar.'/#organisasi',
                'name' => $identitas->nama_perusahaan,
                'url' => $akar,
                'logo' => Seo::alamat($identitas->logo_url),
                'description' => $identitas->meta_deskripsi,
                'email' => $identitas->email,
                'telephone' => Str::after(TautanKontak::telepon($identitas->telepon), 'tel:'),
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => $identitas->alamat, 'addressCountry' => 'ID'],
                'sameAs' => array_values(array_filter([$identitas->link_instagram, $identitas->link_youtube])),
            ], fn (mixed $nilai): bool => filled($nilai)),
            [
                '@type' => 'WebSite',
                '@id' => $akar.'/#situs',
                'url' => $akar,
                'name' => $identitas->nama_perusahaan,
                'inLanguage' => self::BAHASA,
                'publisher' => ['@id' => $akar.'/#organisasi'],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function daftarArtikel(): array
    {
        return self::dokumen([self::remahRoti(['Beranda' => Seo::alamat()], 'Artikel')]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function kategoriArtikel(KategoriArtikel $kategoriArtikel): array
    {
        return self::dokumen([self::remahRoti([
            'Beranda' => Seo::alamat(),
            'Artikel' => Seo::alamat(route('artikel.index', [], false)),
        ], $kategoriArtikel->nama_kategori)]);
    }

    /**
     * Artikel blog beserta remah rotinya. Penulisnya perusahaan karena artikel tidak menyimpan nama penulis.
     *
     * @return array<string, mixed>
     */
    public static function artikel(Artikel $artikel, Identitas $identitas, string $ringkasan): array
    {
        $alamat = Seo::alamat(route('artikel.show', $artikel, false));

        return self::dokumen([
            array_filter([
                '@type' => 'BlogPosting',
                '@id' => $alamat.'#artikel',
                'mainEntityOfPage' => $alamat,
                'headline' => $artikel->judul,
                'description' => $ringkasan,
                'image' => [Seo::alamat($artikel->gambar_url)],
                'datePublished' => $artikel->tanggal->toIso8601String(),
                'dateModified' => $artikel->updated_at?->toIso8601String(),
                'articleSection' => $artikel->kategoriArtikel?->nama_kategori,
                'inLanguage' => self::BAHASA,
                'author' => ['@type' => 'Organization', 'name' => $identitas->nama_perusahaan, 'url' => Seo::alamat()],
            ], fn (mixed $nilai): bool => filled($nilai)),
            self::remahRoti([
                'Beranda' => Seo::alamat(),
                'Artikel' => Seo::alamat(route('artikel.index', [], false)),
            ], $artikel->judul),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function daftarPortofolio(): array
    {
        return self::dokumen([self::remahRoti(['Beranda' => Seo::alamat()], 'Portofolio')]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function portofolio(Portofolio $portofolio): array
    {
        return self::dokumen([self::remahRoti([
            'Beranda' => Seo::alamat(),
            'Portofolio' => Seo::alamat(route('portofolio.index', [], false)),
        ], $portofolio->judul)]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function daftarTutorial(): array
    {
        return self::dokumen([self::remahRoti(['Beranda' => Seo::alamat()], 'Tutorial')]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function kategoriTutorial(KategoriTutorial $kategoriTutorial): array
    {
        return self::dokumen([self::remahRoti([
            'Beranda' => Seo::alamat(),
            'Tutorial' => Seo::alamat(route('tutorial.index', [], false)),
        ], $kategoriTutorial->nama_kategori)]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function tutorial(Tutorial $tutorial): array
    {
        return self::dokumen([self::remahRoti([
            'Beranda' => Seo::alamat(),
            'Tutorial' => Seo::alamat(route('tutorial.index', [], false)),
        ], $tutorial->judul)]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function materiTutorial(Tutorial $tutorial, MateriTutorial $materiTutorial): array
    {
        return self::dokumen([self::remahRoti([
            'Beranda' => Seo::alamat(),
            'Tutorial' => Seo::alamat(route('tutorial.index', [], false)),
            $tutorial->judul => Seo::alamat(route('tutorial.show', $tutorial, false)),
        ], $materiTutorial->judul)]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $graf
     * @return array<string, mixed>
     */
    private static function dokumen(array $graf): array
    {
        return ['@context' => 'https://schema.org', '@graph' => $graf];
    }

    /**
     * Remah roti sampai halaman yang sedang dibuka. Halaman itu sendiri tanpa "item": Google memakai alamatnya.
     *
     * @param  array<string, string>  $jejak  label => alamat halaman induk, dari yang teratas
     * @return array<string, mixed>
     */
    private static function remahRoti(array $jejak, string $halamanIni): array
    {
        $daftar = [];

        foreach ($jejak as $label => $alamat) {
            $daftar[] = ['@type' => 'ListItem', 'position' => count($daftar) + 1, 'name' => $label, 'item' => $alamat];
        }

        $daftar[] = ['@type' => 'ListItem', 'position' => count($daftar) + 1, 'name' => $halamanIni];

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $daftar];
    }
}
