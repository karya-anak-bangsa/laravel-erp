<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SudutTemplate;
use App\Support\KontrasWarna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu-satunya sumber nilai template frontend. Saat ini dari config/tema.php; di Fase 4 langkah 3
 * cukup kelas ini yang dialihkan ke tb_template (template aktif per jenis).
 */
class TemaService
{
    // Cadangan bila config tidak lengkap/tidak valid (mis. cache config lama saat deploy),
    // agar halaman publik tidak pernah gagal dirender karena template.
    private const BAWAAN = [
        'full_color' => ['nama' => 'TKAB Full Color', 'warna_utama' => '#15253F', 'warna_aksen' => '#CB1839', 'nada_dasar' => null, 'font' => 'plus_jakarta_sans', 'sudut' => 'sedang'],
        'monochrome' => ['nama' => 'TKAB Monochrome', 'warna_utama' => null, 'warna_aksen' => null, 'nada_dasar' => 'netral', 'font' => 'geist', 'sudut' => 'sedang'],
    ];

    /** @var array<string, array{nama: string, warna_utama: string|null, warna_aksen: string|null, nada_dasar: NadaDasar|null, font: FontTemplate, sudut: SudutTemplate}> */
    private array $template = [];

    public function jenisDariRequest(Request $request): JenisTemplate
    {
        return JenisTemplate::dariCookie($request->cookie(config('tema.cookie', 'tema')));
    }

    /**
     * @return array{nama: string, warna_utama: string|null, warna_aksen: string|null, nada_dasar: NadaDasar|null, font: FontTemplate, sudut: SudutTemplate}
     */
    public function templateAktif(JenisTemplate $jenis): array
    {
        return $this->template[$jenis->value] ??= $this->muat($jenis);
    }

    /**
     * Isi <style id="token-template">: token dasar kedua template aktif. Semua nilai berasal dari enum
     * atau hex yang sudah divalidasi KontrasWarna, sehingga aman dirender tanpa escape.
     */
    public function tokenCss(): string
    {
        $warna = $this->templateAktif(JenisTemplate::FullColor);
        $mono = $this->templateAktif(JenisTemplate::Monochrome);

        $utama = (string) $warna['warna_utama'];
        $aksen = (string) $warna['warna_aksen'];
        $nada = $mono['nada_dasar'] ?? NadaDasar::Netral;

        $tokenWarna = [
            '--warna-utama' => $utama,
            '--warna-aksen' => $aksen,
            '--utama-teks' => KontrasWarna::pastikanKontras($utama),
            '--aksen-teks' => KontrasWarna::pastikanKontras($aksen),
            '--aksen-kontras' => KontrasWarna::teksDiAtas($aksen),
            '--font-tema' => '"'.$warna['font']->family().'"',
            ...$this->tokenSudut($warna['sudut']),
        ];

        $tokenMono = [
            ...array_combine(array_map(fn (int $t): string => "--nada-{$t}", NadaDasar::TINGKAT), $nada->skala()),
            '--font-tema' => '"'.$mono['font']->family().'"',
            ...$this->tokenSudut($mono['sudut']),
        ];

        return 'html[data-tema=full_color] { '.$this->deklarasi($tokenWarna)." }\n"
            .'html[data-tema=monochrome] { '.$this->deklarasi($tokenMono).' }';
    }

    /**
     * Alias @fonts yang dimuat halaman publik: font kedua template (agar ganti tema tanpa memuat ulang)
     * ditambah Geist Mono untuk label bergaya mono.
     *
     * @return list<string>
     */
    public function aliasFont(): array
    {
        return array_values(array_unique([
            $this->templateAktif(JenisTemplate::FullColor)['font']->alias(),
            $this->templateAktif(JenisTemplate::Monochrome)['font']->alias(),
            'geist-mono',
        ]));
    }

    /**
     * @return array{nama: string, warna_utama: string|null, warna_aksen: string|null, nada_dasar: NadaDasar|null, font: FontTemplate, sudut: SudutTemplate}
     */
    private function muat(JenisTemplate $jenis): array
    {
        $config = config("tema.template.{$jenis->value}");

        try {
            return $this->rakit($jenis, is_array($config) ? $config : []);
        } catch (Throwable $e) {
            Log::warning("Template {$jenis->value} di config/tema.php tidak valid, memakai bawaan.", ['galat' => $e->getMessage()]);

            return $this->rakit($jenis, self::BAWAAN[$jenis->value]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{nama: string, warna_utama: string|null, warna_aksen: string|null, nada_dasar: NadaDasar|null, font: FontTemplate, sudut: SudutTemplate}
     */
    private function rakit(JenisTemplate $jenis, array $data): array
    {
        $warna = $jenis === JenisTemplate::FullColor;

        return [
            'nama' => (string) ($data['nama'] ?? ''),
            'warna_utama' => $warna ? KontrasWarna::normalisasi((string) ($data['warna_utama'] ?? '')) : null,
            'warna_aksen' => $warna ? KontrasWarna::normalisasi((string) ($data['warna_aksen'] ?? '')) : null,
            'nada_dasar' => $warna ? null : NadaDasar::from((string) ($data['nada_dasar'] ?? '')),
            'font' => FontTemplate::from((string) ($data['font'] ?? '')),
            'sudut' => SudutTemplate::from((string) ($data['sudut'] ?? '')),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function tokenSudut(SudutTemplate $sudut): array
    {
        $token = [];

        foreach ($sudut->token() as $nama => $nilai) {
            $token["--sudut-{$nama}"] = $nilai;
        }

        return $token;
    }

    /**
     * @param  array<string, string>  $token
     */
    private function deklarasi(array $token): string
    {
        return implode(' ', array_map(fn (string $nama, string $nilai): string => "{$nama}: {$nilai};", array_keys($token), $token));
    }
}
