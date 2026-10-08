<?php

use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
| Editor WYSIWYG dipakai untuk teks yang tampil di frontend (deskripsi hero, layanan,
| portofolio, keterangan layanan, serta jawaban FAQ); teks internal (identitas) tetap textarea biasa.
*/

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

/**
 * [rute tambah, data valid, kolom editor => batas teks terlihat]
 *
 * @return array<string, array{string, Closure(): array<string, mixed>, array<string, int>}>
 */
function modulEditor(): array
{
    $gambar = fn () => UploadedFile::fake()->image('gambar.webp', 800, 600);

    return [
        'hero' => ['admin.hero', fn () => [
            'judul' => 'Solusi Digital', 'gambar' => $gambar(), 'keyword' => ['Website'], 'cta' => [], 'status_aktif' => '0',
        ], ['deskripsi' => 1000]],
        'layanan' => ['admin.layanan', fn () => [
            'judul' => 'Pembuatan Website', 'gambar' => $gambar(), 'urutan_ke' => 1,
        ], ['deskripsi' => 1000, 'keterangan' => 5000]],
        'portofolio' => ['admin.portofolio', fn () => [
            'judul' => 'Website Sekolah', 'kategori' => 'Website', 'gambar' => $gambar(),
        ], ['deskripsi' => 5000]],
        'faq' => ['admin.faq', fn () => [
            'pertanyaan' => 'Berapa lama pembuatan website?', 'urutan_ke' => 1,
        ], ['jawaban' => 2000]],
    ];
}

it('menampilkan editor WYSIWYG di form tambah untuk teks yang tampil di frontend', function (string $rute, Closure $data, array $kolom) {
    $respons = $this->actingAs($this->admin)->get(route("{$rute}.create"))->assertOk();

    foreach (array_keys($kolom) as $nama) {
        $respons->assertSee('data-editor-sumber', false)
            ->assertSee('name="'.$nama.'"', false)
            ->assertSee('data-perintah="bold"', false);
    }

    // Semua editor setinggi 5 baris (pilihan pemilik); textarea cadangannya ikut rows="5".
    expect(substr_count($respons->getContent(), 'data-editor>'))->toBe(count($kolom))
        ->and(substr_count($respons->getContent(), 'rows="5" data-editor-sumber'))->toBe(count($kolom));
})->with(modulEditor());

it('tidak memakai editor WYSIWYG di form identitas', function () {
    Identitas::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.identitas.edit'))
        ->assertOk()
        ->assertSee('name="meta_deskripsi"', false)
        ->assertSee('name="alamat"', false)
        ->assertDontSee('data-editor', false);
});

it('menyimpan HTML editor yang sudah disanitasi', function (string $rute, Closure $data, array $kolom) {
    $isian = [];
    foreach (array_keys($kolom) as $nama) {
        $isian[$nama] = '<p onclick="x()">Isi <strong>tebal</strong><script>alert(1)</script></p><ul><li><p>poin</p></li></ul>';
    }

    $this->actingAs($this->admin)
        ->post(route("{$rute}.store"), [...$data(), ...$isian])
        ->assertSessionHasNoErrors();

    $model = match ($rute) {
        'admin.hero' => Hero::sole(),
        'admin.layanan' => Layanan::sole(),
        'admin.portofolio' => Portofolio::sole(),
        'admin.faq' => Faq::sole(),
    };

    foreach (array_keys($kolom) as $nama) {
        expect($model->{$nama})->toBe('<p>Isi <strong>tebal</strong></p><ul><li><p>poin</p></li></ul>');
    }
})->with(modulEditor());

it('menghitung batas panjang dari teks terlihat, bukan markup HTML', function (string $rute, Closure $data, array $kolom) {
    foreach ($kolom as $nama => $maks) {
        // Pas di batas: markup <p><strong> tidak ikut terhitung.
        $this->actingAs($this->admin)
            ->from(route("{$rute}.create"))
            ->post(route("{$rute}.store"), [...$data(), ...array_fill_keys(array_keys($kolom), '<p>Isi</p>'), $nama => '<p><strong>'.str_repeat('a', $maks).'</strong></p>'])
            ->assertSessionDoesntHaveErrors($nama);

        $this->actingAs($this->admin)
            ->from(route("{$rute}.create"))
            ->post(route("{$rute}.store"), [...$data(), $nama => '<p>'.str_repeat('a', $maks + 1).'</p>'])
            ->assertSessionHasErrors([$nama => "Kolom {$nama} tidak boleh lebih dari ".$maks.' karakter.']);
    }
})->with(modulEditor());

it('menganggap editor kosong sebagai belum diisi', function (string $rute, Closure $data, array $kolom) {
    // Kolom editor pertama di setiap modul adalah isian wajib.
    $wajib = array_key_first($kolom);

    $this->actingAs($this->admin)
        ->post(route("{$rute}.store"), [...$data(), $wajib => '<p></p>'])
        ->assertSessionHasErrors([$wajib => "Kolom {$wajib} wajib diisi."]);
})->with(modulEditor());

it('menyimpan keterangan layanan yang kosong di editor sebagai null', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.layanan.store'), [
            'judul' => 'Pelatihan IT', 'deskripsi' => '<p>Kelas.</p>', 'keterangan' => '<p><br></p>',
            'gambar' => UploadedFile::fake()->image('gambar.webp'), 'urutan_ke' => 1,
        ])
        ->assertSessionHasNoErrors();

    expect(Layanan::sole()->keterangan)->toBeNull();
});

it('menampilkan ringkasan teks polos di tabel dan HTML berformat di modal detail', function () {
    Portofolio::factory()->create(['deskripsi' => '<p>Proyek <strong>unggulan</strong></p><ul><li><p>fitur satu</p></li></ul>']);

    $this->actingAs($this->admin)
        ->get(route('admin.portofolio.index'))
        ->assertSee('<div class="card-subtitle">Proyek unggulan fitur satu</div>', false)
        ->assertSee('<td class="konten-html"><p>Proyek <strong>unggulan</strong></p><ul><li><p>fitur satu</p></li></ul></td>', false);
});
