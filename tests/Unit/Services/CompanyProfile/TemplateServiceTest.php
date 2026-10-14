<?php

use App\Enums\CompanyProfile\JenisTemplate;
use App\Models\CompanyProfile\Template;
use App\Services\CompanyProfile\TemplateService;

beforeEach(function () {
    $this->layanan = app(TemplateService::class);
});

function dataTemplateLayanan(array $timpa = []): array
{
    return [
        'nama' => 'Template Uji',
        'jenis' => 'full_color',
        'warna_utama' => '#0c4a6e',
        'warna_aksen' => '#0ea5e9',
        'nada_dasar' => null,
        'font' => 'geist',
        'sudut' => 'sedang',
        'skala' => 'ringkas',
        'lebar_konten' => 'lebar',
        'ketebalan_judul' => 'semibold',
        'bayangan' => 'halus',
        'status_aktif' => true,
        ...$timpa,
    ];
}

it('menyimpan template baru beserta pilihan bayangannya', function () {
    $template = $this->layanan->simpan(dataTemplateLayanan(['bayangan' => 'tegas']));

    expect($template->exists)->toBeTrue()
        ->and($template->refresh()->bayangan->value)->toBe('tegas');
});

it('hanya menyisakan satu template aktif per jenis, termasuk terhadap template yang terhapus', function () {
    $aktif = Template::factory()->aktif()->create();
    $terhapus = Template::factory()->aktif()->create();
    $terhapus->delete();
    $mono = Template::factory()->monochrome()->aktif()->create();

    $baru = $this->layanan->simpan(dataTemplateLayanan());

    expect($baru->status_aktif)->toBeTrue()
        ->and($aktif->refresh()->status_aktif)->toBeFalse()
        ->and(Template::withTrashed()->find($terhapus->id_template)->status_aktif)->toBeFalse()
        ->and($mono->refresh()->status_aktif)->toBeTrue();
});

it('tidak mengubah template aktif lain saat template disimpan nonaktif', function () {
    $aktif = Template::factory()->aktif()->create();

    $this->layanan->simpan(dataTemplateLayanan(['status_aktif' => false]));

    expect($aktif->refresh()->status_aktif)->toBeTrue();
});

it('mengaktifkan template lewat perubahan dan menonaktifkan template sejenis', function () {
    $aktif = Template::factory()->monochrome()->aktif()->create();
    $template = Template::factory()->monochrome()->create();

    $this->layanan->perbarui($template, dataTemplateLayanan([
        'nama' => $template->nama,
        'jenis' => JenisTemplate::Monochrome->value,
        'warna_utama' => null,
        'warna_aksen' => null,
        'nada_dasar' => 'zinc',
    ]));

    expect($template->refresh()->status_aktif)->toBeTrue()
        ->and($template->nada_dasar->value)->toBe('zinc')
        ->and($aktif->refresh()->status_aktif)->toBeFalse();
});
