<?php

namespace App\Services\CompanyProfile;

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\KetebalanJudul;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use App\Models\CompanyProfile\Template;
use App\Support\KontrasWarna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Satu-satunya sumber nilai template frontend: template aktif per jenis di tb_template (Pengaturan Sistem ›
 * Manajemen Template). Bila database tidak terbaca atau belum ada template aktif, nilainya dari config/tema.php
 * lalu nilai bawaan, agar halaman publik (termasuk halaman error) tidak pernah gagal karena template.
 *
 * @phpstan-type NilaiTemplate array{nama: string, warna_utama: string|null, warna_aksen: string|null, nada_dasar: NadaDasar|null, font: FontTemplate, sudut: SudutTemplate, skala: SkalaTemplate, lebar_konten: LebarKonten, ketebalan_judul: KetebalanJudul, bayangan: BayanganTemplate}
 */
class TemaService
{
    /** Atribut request berisi Template (belum tentu tersimpan) yang sedang dipratinjau admin. */
    public const PRATINJAU = 'tema.pratinjau';

    // Cadangan terakhir bila config tidak lengkap/tidak valid (mis. cache config lama saat deploy).
    private const BAWAAN = [
        'full_color' => ['nama' => 'TKAB Full Color', 'warna_utama' => '#15253F', 'warna_aksen' => '#CB1839', 'nada_dasar' => null, 'font' => 'geist', 'sudut' => 'sedang', 'skala' => 'ringkas', 'lebar_konten' => 'lebar', 'ketebalan_judul' => 'semibold', 'bayangan' => 'halus'],
        'monochrome' => ['nama' => 'TKAB Monochrome', 'warna_utama' => null, 'warna_aksen' => null, 'nada_dasar' => 'netral', 'font' => 'geist', 'sudut' => 'sedang', 'skala' => 'ringkas', 'lebar_konten' => 'lebar', 'ketebalan_judul' => 'semibold', 'bayangan' => 'halus'],
    ];

    /** @var array<string, NilaiTemplate> */
    private array $template = [];

    /** @var array<string, array<string, string>>|null nilai mentah template aktif per jenis, dibaca sekali per halaman */
    private ?array $aktifDatabase = null;

    private ?Template $pratinjau = null;

    /**
     * Mode pratinjau (TemplateController::pratinjau): template ini menggantikan template aktif sejenisnya dan
     * menentukan tema halaman, tanpa disimpan.
     */
    public function pratinjau(?Template $template): static
    {
        $this->pratinjau = $template;
        $this->template = [];

        return $this;
    }

    public function jenisDariRequest(Request $request): JenisTemplate
    {
        return $this->pratinjau->jenis ?? JenisTemplate::dariCookie($request->cookie(config('tema.cookie', 'tema')));
    }

    /**
     * @return NilaiTemplate
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
            '--ketebalan-judul' => $warna['ketebalan_judul']->nilai(),
            ...$this->tokenSudut($warna['sudut']),
            ...$warna['bayangan']->token(JenisTemplate::FullColor),
            ...$this->tokenSkala($warna['skala']),
            '--lebar-konten' => LebarKonten::DASAR,
        ];

        $tokenMono = [
            ...array_combine(array_map(fn (int $t): string => "--nada-{$t}", NadaDasar::TINGKAT), $nada->skala()),
            '--font-tema' => '"'.$mono['font']->family().'"',
            '--ketebalan-judul' => $mono['ketebalan_judul']->nilai(),
            ...$this->tokenSudut($mono['sudut']),
            ...$mono['bayangan']->token(JenisTemplate::Monochrome),
            ...$this->tokenSkala($mono['skala']),
            '--lebar-konten' => LebarKonten::DASAR,
        ];

        // Monitor ≥1920px (24" ke atas): skala satu tingkat lebih besar dan lebar konten pilihan template.
        $lebarWarna = [...$this->tokenSkala($warna['skala']->naik()), '--lebar-konten' => $warna['lebar_konten']->nilai()];
        $lebarMono = [...$this->tokenSkala($mono['skala']->naik()), '--lebar-konten' => $mono['lebar_konten']->nilai()];
        $tokenLebar = 'html[data-tema=full_color] { '.$this->deklarasi($lebarWarna).' } '
            .'html[data-tema=monochrome] { '.$this->deklarasi($lebarMono).' }';

        return 'html[data-tema=full_color] { '.$this->deklarasi($tokenWarna)." }\n"
            .'html[data-tema=monochrome] { '.$this->deklarasi($tokenMono)." }\n"
            ."@media (min-width: 1920px) { {$tokenLebar} }";
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
     * @return NilaiTemplate
     */
    private function muat(JenisTemplate $jenis): array
    {
        if ($this->pratinjau?->jenis === $jenis) {
            return $this->rakit($jenis, $this->pratinjau->nilaiTampilan());
        }

        foreach (['database' => $this->dariDatabase($jenis), 'config/tema.php' => config("tema.template.{$jenis->value}")] as $sumber => $nilai) {
            if (! is_array($nilai)) {
                continue;
            }

            try {
                return $this->rakit($jenis, $nilai);
            } catch (Throwable $e) {
                Log::warning("Template {$jenis->value} dari {$sumber} tidak valid, memakai cadangan berikutnya.", ['galat' => $e->getMessage()]);
            }
        }

        return $this->rakit($jenis, self::BAWAAN[$jenis->value]);
    }

    /**
     * @return array<string, string>|null
     */
    private function dariDatabase(JenisTemplate $jenis): ?array
    {
        if ($this->aktifDatabase === null) {
            try {
                // Satu query untuk kedua jenis.
                $this->aktifDatabase = Template::query()->aktif()->orderBy('id_template')->get()
                    ->mapWithKeys(fn (Template $template): array => [$template->jenis->value => $template->nilaiTampilan()])
                    ->all();
            } catch (Throwable $e) {
                Log::warning('Template aktif tidak terbaca dari database, memakai config/tema.php.', ['galat' => $e->getMessage()]);
                $this->aktifDatabase = [];
            }
        }

        return $this->aktifDatabase[$jenis->value] ?? null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return NilaiTemplate
     */
    private function rakit(JenisTemplate $jenis, array $data): array
    {
        $warna = $jenis === JenisTemplate::FullColor;
        $bawaan = self::BAWAAN[$jenis->value];

        return [
            'nama' => (string) ($data['nama'] ?? ''),
            'warna_utama' => $warna ? KontrasWarna::normalisasi((string) ($data['warna_utama'] ?? '')) : null,
            'warna_aksen' => $warna ? KontrasWarna::normalisasi((string) ($data['warna_aksen'] ?? '')) : null,
            'nada_dasar' => $warna ? null : NadaDasar::from((string) ($data['nada_dasar'] ?? '')),
            'font' => FontTemplate::from((string) ($data['font'] ?? '')),
            'sudut' => SudutTemplate::from((string) ($data['sudut'] ?? '')),
            // Kunci yang lebih baru: bila belum ada (mis. cache config lama), pakai bawaan tanpa menggugurkan nilai lain.
            'skala' => SkalaTemplate::tryFrom((string) ($data['skala'] ?? '')) ?? SkalaTemplate::from($bawaan['skala']),
            'lebar_konten' => LebarKonten::tryFrom((string) ($data['lebar_konten'] ?? '')) ?? LebarKonten::from($bawaan['lebar_konten']),
            'ketebalan_judul' => KetebalanJudul::tryFrom((string) ($data['ketebalan_judul'] ?? '')) ?? KetebalanJudul::from($bawaan['ketebalan_judul']),
            'bayangan' => BayanganTemplate::tryFrom((string) ($data['bayangan'] ?? '')) ?? BayanganTemplate::from($bawaan['bayangan']),
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
     * @return array<string, string>
     */
    private function tokenSkala(SkalaTemplate $skala): array
    {
        $nilai = $skala->token();

        return [
            '--tinggi-navbar' => $nilai['navbar'],
            '--tinggi-logo' => $nilai['logo'],
            '--teks-menu' => $nilai['menu'],
            '--tinggi-tombol' => $nilai['tombol'],
            '--teks-tombol' => $nilai['teks-tombol'],
            '--teks-judul-seksi' => $nilai['judul-seksi'],
        ];
    }

    /**
     * @param  array<string, string>  $token
     */
    private function deklarasi(array $token): string
    {
        return implode(' ', array_map(fn (string $nama, string $nilai): string => "{$nama}: {$nilai};", array_keys($token), $token));
    }
}
