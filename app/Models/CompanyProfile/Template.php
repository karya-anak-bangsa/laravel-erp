<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\KetebalanJudul;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use Database\Factories\CompanyProfile\TemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory, SoftDeletes;

    /** Kolom pengaturan tampilan; kuncinya sama dengan config/tema.php sehingga dibaca TemaService dengan cara yang sama. */
    public const KOLOM_TAMPILAN = ['nama', 'jenis', 'warna_utama', 'warna_aksen', 'nada_dasar', 'font', 'sudut', 'skala', 'lebar_konten', 'ketebalan_judul', 'bayangan'];

    protected $table = 'tb_template';

    protected $primaryKey = 'id_template';

    protected $fillable = [...self::KOLOM_TAMPILAN, 'status_aktif'];

    protected function casts(): array
    {
        return [
            'jenis' => JenisTemplate::class,
            'nada_dasar' => NadaDasar::class,
            'font' => FontTemplate::class,
            'sudut' => SudutTemplate::class,
            'skala' => SkalaTemplate::class,
            'lebar_konten' => LebarKonten::class,
            'ketebalan_judul' => KetebalanJudul::class,
            'bayangan' => BayanganTemplate::class,
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * Template yang dipakai frontend: satu per jenis, dijaga TemplateService.
     *
     * @param  Builder<Template>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status_aktif', true);
    }

    /**
     * Nilai mentah kolom tampilan (string, tanpa NULL) untuk TemaService dan parameter tautan pratinjau.
     *
     * @return array<string, string>
     */
    public function nilaiTampilan(): array
    {
        $nilai = [];

        foreach (self::KOLOM_TAMPILAN as $kolom) {
            if (filled($this->attributes[$kolom] ?? null)) {
                $nilai[$kolom] = (string) $this->attributes[$kolom];
            }
        }

        return $nilai;
    }
}
