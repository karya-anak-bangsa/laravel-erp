<?php

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\TutorialService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->tutorial = app(TutorialService::class);
});

function dataTutorialUji(array $timpa = []): array
{
    return [
        'jenis_program' => 'bootcamp',
        'judul' => 'Tutorial Uji',
        'deskripsi' => '<p>Pengantar.</p>',
        'gambar' => UploadedFile::fake()->image('sampul.webp'),
        'status_publikasi' => StatusPublikasi::Draf->value,
        ...$timpa,
    ];
}

it('menyimpan tutorial baru beserta slug dan sampulnya', function () {
    $tutorial = $this->tutorial->simpan(dataTutorialUji());

    expect($tutorial->exists)->toBeTrue()
        ->and($tutorial->slug)->toBe('tutorial-uji')
        ->and($tutorial->gambar)->toStartWith('company-profile/tutorial/');
    Storage::disk('public')->assertExists($tutorial->gambar);
});

it('memberi akhiran angka bila slug sudah dipakai tutorial lain, termasuk yang terhapus', function () {
    Tutorial::factory()->create(['slug' => 'tutorial-uji']);
    Tutorial::factory()->create(['slug' => 'tutorial-uji-2'])->delete();
    Artikel::factory()->create(['slug' => 'tutorial-lain']);

    expect($this->tutorial->buatSlug('Tutorial Uji'))->toBe('tutorial-uji-3')
        // Slug artikel tidak dihitung bentrok.
        ->and($this->tutorial->buatSlug('Tutorial Lain'))->toBe('tutorial-lain')
        ->and($this->tutorial->buatSlug('???'))->toBe('tutorial');
});

it('memperbarui slug tutorial draf saat judul berubah, termasuk saat langsung diterbitkan', function () {
    $tutorial = Tutorial::factory()->draf()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

    $this->tutorial->perbarui($tutorial, dataTutorialUji(['judul' => 'Judul Baru', 'gambar' => null, 'status_publikasi' => 'terbit']));

    expect($tutorial->refresh()->slug)->toBe('judul-baru')
        ->and($tutorial->status_publikasi)->toBe(StatusPublikasi::Terbit);
});

it('mengunci slug tutorial yang sudah terbit meskipun judulnya diubah', function (string $statusBaru) {
    $tutorial = Tutorial::factory()->terbit()->create(['judul' => 'Judul Terbit', 'slug' => 'judul-terbit']);

    $this->tutorial->perbarui($tutorial, dataTutorialUji(['judul' => 'Judul Diganti', 'gambar' => null, 'status_publikasi' => $statusBaru]));

    expect($tutorial->refresh()->slug)->toBe('judul-terbit')
        ->and($tutorial->judul)->toBe('Judul Diganti');
})->with(['tetap terbit' => 'terbit', 'dikembalikan ke draf' => 'draf']);

it('mengganti sampul dan menghapus sampul lama', function () {
    Storage::disk('public')->put('company-profile/tutorial/lama.webp', 'isi');
    $tutorial = Tutorial::factory()->create(['gambar' => 'company-profile/tutorial/lama.webp']);

    $this->tutorial->perbarui($tutorial, dataTutorialUji(['gambar' => UploadedFile::fake()->image('baru.jpg')]));

    expect($tutorial->refresh()->gambar)->toEndWith('.webp');
    Storage::disk('public')->assertMissing('company-profile/tutorial/lama.webp');
});

it('menghapus tutorial beserta materinya dalam satu transaksi', function () {
    $tutorial = Tutorial::factory()->create();
    MateriTutorial::factory()->count(3)->for($tutorial)->create();
    $lain = MateriTutorial::factory()->create();

    $this->tutorial->hapus($tutorial);

    expect(Tutorial::find($tutorial->id_tutorial))->toBeNull()
        ->and(MateriTutorial::withTrashed()->where('id_tutorial', $tutorial->id_tutorial)->whereNotNull('deleted_at')->count())->toBe(3)
        ->and($lain->refresh()->trashed())->toBeFalse();
});
