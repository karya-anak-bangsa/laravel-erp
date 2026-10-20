<?php

use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\MateriTutorialService;

beforeEach(function () {
    $this->materi = app(MateriTutorialService::class);
    $this->tutorial = Tutorial::factory()->draf()->create();
});

function dataMateriUji(array $timpa = []): array
{
    return [
        'id_tutorial' => Tutorial::query()->value('id_tutorial'),
        'judul' => 'Instalasi',
        'deskripsi' => '<p>Isi materi.</p>',
        'urutan_ke' => null,
        ...$timpa,
    ];
}

it('menaruh materi baru tanpa urutan setelah materi terakhir tutorialnya', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => 6]);
    // Materi tutorial lain dan materi terhapus tidak ikut dihitung.
    MateriTutorial::factory()->create(['urutan_ke' => 40]);
    MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => 50])->delete();

    expect($this->materi->simpan(dataMateriUji())->urutan_ke)->toBe(7)
        ->and($this->materi->simpan(dataMateriUji(['judul' => 'Lain', 'urutan_ke' => 2]))->urutan_ke)->toBe(2);
});

it('membatasi urutan otomatis pada angka maksimum', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => MateriTutorial::URUTAN_MAKS]);

    expect($this->materi->simpan(dataMateriUji())->urutan_ke)->toBe(MateriTutorial::URUTAN_MAKS);
});

it('membuat slug yang unik per tutorial, termasuk terhadap materi terhapus', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['slug' => 'instalasi']);
    MateriTutorial::factory()->for($this->tutorial)->create(['slug' => 'instalasi-2'])->delete();
    $lain = Tutorial::factory()->create();
    MateriTutorial::factory()->for($lain)->create(['slug' => 'routing']);

    expect($this->materi->buatSlug('Instalasi', $this->tutorial->id_tutorial))->toBe('instalasi-3')
        // Tutorial lain boleh memakai slug yang sama.
        ->and($this->materi->buatSlug('Routing', $this->tutorial->id_tutorial))->toBe('routing')
        ->and($this->materi->buatSlug('???', $this->tutorial->id_tutorial))->toBe('materi');
});

it('memperbarui slug materi saat judul berubah selama tutorialnya masih draf', function () {
    $materi = MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->materi->perbarui($materi, ['judul' => 'Judul Baru', 'deskripsi' => '<p>Isi.</p>', 'urutan_ke' => 1]);

    expect($materi->refresh()->slug)->toBe('judul-baru');
});

it('mengunci slug materi begitu tutorialnya terbit', function () {
    $terbit = Tutorial::factory()->terbit()->create();
    $materi = MateriTutorial::factory()->for($terbit)->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->materi->perbarui($materi, ['judul' => 'Judul Baru', 'deskripsi' => '<p>Isi.</p>', 'urutan_ke' => 1]);

    expect($materi->refresh()->slug)->toBe('judul-lama')
        ->and($materi->judul)->toBe('Judul Baru');
});
