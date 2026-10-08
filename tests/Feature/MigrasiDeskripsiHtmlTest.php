<?php

use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;

function migrasiDeskripsiHtml(): object
{
    return require database_path('migrations/2026_10_09_000004_ubah_deskripsi_teks_polos_ke_html.php');
}

it('mengubah deskripsi teks polos lama menjadi paragraf HTML yang aman', function () {
    $hero = Hero::factory()->create(['deskripsi' => "Baris satu\nBaris dua & <b>\n\nParagraf dua"]);
    $layanan = Layanan::factory()->create(['deskripsi' => 'Ringkasan.', 'keterangan' => null]);
    $portofolio = Portofolio::factory()->create(['deskripsi' => 'Proyek lama.']);
    $portofolio->delete();

    migrasiDeskripsiHtml()->up();

    expect($hero->refresh()->deskripsi)->toBe('<p>Baris satu<br>Baris dua &amp; &lt;b&gt;</p><p>Paragraf dua</p>')
        ->and($layanan->refresh()->deskripsi)->toBe('<p>Ringkasan.</p>')
        ->and($layanan->keterangan)->toBeNull()
        ->and(Portofolio::withTrashed()->find($portofolio->id_portofolio)->deskripsi)->toBe('<p>Proyek lama.</p>');
});

it('tidak mengubah deskripsi yang sudah berupa HTML', function () {
    $hero = Hero::factory()->create(['deskripsi' => '<p>Sudah <strong>HTML</strong></p>']);

    migrasiDeskripsiHtml()->up();

    expect($hero->refresh()->deskripsi)->toBe('<p>Sudah <strong>HTML</strong></p>');
});

it('mengembalikan HTML menjadi teks polos saat rollback', function () {
    $hero = Hero::factory()->create(['deskripsi' => '<p>Satu &amp; <strong>dua</strong><br>tiga</p><p>Empat</p>']);

    migrasiDeskripsiHtml()->down();

    expect($hero->refresh()->deskripsi)->toBe("Satu & dua\ntiga\n\nEmpat");
});
