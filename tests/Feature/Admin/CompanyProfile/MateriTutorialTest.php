<?php

use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Models\Pengguna;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
    $this->tutorial = Tutorial::factory()->draf()->create(['judul' => 'Tutorial Laravel 13']);
});

function dataMateriValid(array $timpa = []): array
{
    return [
        'id_tutorial' => Tutorial::query()->value('id_tutorial'),
        'judul' => 'Instalasi dan Konfigurasi',
        'deskripsi' => '<h2>Membuat proyek</h2><p>Jalankan <code>composer create-project</code>.</p>',
        'urutan_ke' => '',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $materi = MateriTutorial::factory()->create();

    $this->get(route('admin.materi-tutorial.index'))->assertRedirect(route('login'));
    $this->get(route('admin.materi-tutorial.create'))->assertRedirect(route('login'));
    $this->post(route('admin.materi-tutorial.store'), dataMateriValid())->assertRedirect(route('login'));
    $this->get(route('admin.materi-tutorial.edit', $materi))->assertRedirect(route('login'));
    $this->put(route('admin.materi-tutorial.update', $materi), dataMateriValid())->assertRedirect(route('login'));
    $this->delete(route('admin.materi-tutorial.destroy', $materi))->assertRedirect(route('login'));
});

it('mengelompokkan materi per tutorial terbaru lalu menurut urutannya', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Materi kedua', 'urutan_ke' => 2]);
    MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Materi pertama', 'urutan_ke' => 1]);
    $baru = Tutorial::factory()->create(['judul' => 'Tutorial Flutter']);
    MateriTutorial::factory()->for($baru)->create(['judul' => 'Materi Flutter', 'urutan_ke' => 5]);

    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index'))
        ->assertOk()
        ->assertSeeInOrder(['Materi Flutter', 'Tutorial Flutter', 'Materi pertama', 'Tutorial Laravel 13', 'Materi kedua'])
        ->assertSee('class="nav-sublink active" href="'.route('admin.materi-tutorial.index').'"', false)
        ->assertSee('data-detail-title="Detail Materi Tutorial"', false);
});

it('menyaring materi per tutorial dan meneruskan tutorialnya ke tombol tambah', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Materi Laravel']);
    MateriTutorial::factory()->create(['judul' => 'Materi lain']);

    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index', ['tutorial' => $this->tutorial->id_tutorial]))
        ->assertSee('Materi Laravel')
        ->assertDontSee('Materi lain')
        ->assertSee('<option value="'.$this->tutorial->id_tutorial.'" selected>Tutorial Laravel 13</option>', false)
        ->assertSee('href="'.route('admin.materi-tutorial.create', ['tutorial' => $this->tutorial->id_tutorial]).'"', false);
});

it('menyediakan tombol Buka halaman di modal detail hanya untuk materi tutorial terbit', function () {
    $terbit = Tutorial::factory()->terbit()->create(['slug' => 'tutorial-terbit']);
    MateriTutorial::factory()->for($terbit)->create(['slug' => 'materi-tayang']);
    MateriTutorial::factory()->for($this->tutorial)->create(['slug' => 'materi-draf']);

    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index'))
        ->assertSee('/tutorial/tutorial-terbit/materi-tayang')
        ->assertSee('href="'.route('materi-tutorial.show', ['tutorial-terbit', 'materi-tayang']).'" target="_blank" rel="noopener"', false)
        ->assertDontSee('href="'.route('materi-tutorial.show', [$this->tutorial->slug, 'materi-draf']).'"', false)
        ->assertSee('Belum tayang (tutorial masih draf)');
});

it('mencari materi berdasarkan judul atau isi', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Routing', 'deskripsi' => '<p>Rute pertama.</p>']);
    MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Migrasi', 'deskripsi' => '<p>Tabel buku.</p>']);

    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index', ['q' => 'tabel buku']))
        ->assertSee('Migrasi')
        ->assertDontSee('Rute pertama');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index'))
        ->assertSee('Belum ada materi tutorial');

    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.index', ['q' => 'tidak-ada']))
        ->assertSee('Materi tutorial tidak ditemukan');
});

it('menampilkan editor isi materi dengan sub-judul, kode, dan setinggi layar', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.create'))
        ->assertOk()
        ->assertSee('class="editor editor-layar" data-editor data-judul-bagian data-kode>', false)
        ->assertSeeInOrder(['data-perintah="judul2"', 'data-perintah="orderedList"', 'data-perintah="kode"', 'data-perintah="blokKode"', 'data-perintah="rataKiri"'], false)
        ->assertSee('/30.000 karakter', false)
        ->assertSee('<option value="'.$this->tutorial->id_tutorial.'" >Tutorial Laravel 13</option>', false);
});

it('memilih tutorial dan urutan berikutnya dari tautan daftar yang disaring', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => 4]);

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.create', ['tutorial' => $this->tutorial->id_tutorial]))
        ->assertSee('<option value="'.$this->tutorial->id_tutorial.'" selected>Tutorial Laravel 13</option>', false);

    expect($respons->getContent())->toMatch('#name="urutan_ke"\s+value="5"#');

    // Tutorial yang tidak ada diabaikan.
    $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.create', ['tutorial' => 999999]))
        ->assertOk()
        ->assertDontSee(' selected>Tutorial Laravel 13', false);
});

it('menambah materi di akhir tutorialnya beserta slug dan kodenya', function () {
    MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => 3]);

    $this->actingAs($this->admin)
        ->post(route('admin.materi-tutorial.store'), dataMateriValid([
            'deskripsi' => "<p>Jalankan <code>php artisan migrate</code>.</p><pre><code class=\"language-php\">Route::get('/buku', fn () =&gt; 'ok');\n    // indentasi</code></pre><script>alert(1)</script>",
        ]))
        ->assertRedirect(route('admin.materi-tutorial.index', ['tutorial' => $this->tutorial->id_tutorial]))
        ->assertSessionHas('success');

    $materi = MateriTutorial::query()->where('judul', 'Instalasi dan Konfigurasi')->sole();
    expect($materi->slug)->toBe('instalasi-dan-konfigurasi')
        ->and($materi->urutan_ke)->toBe(4)
        ->and($materi->tutorial->is($this->tutorial))->toBeTrue()
        // Kode sebaris & blok kode (beserta baris barunya) dipertahankan; kelas bahasa dan skrip dibuang.
        ->and($materi->deskripsi)->toBe("<p>Jalankan <code>php artisan migrate</code>.</p><pre><code>Route::get(&#039;/buku&#039;, fn () &#61;&gt; &#039;ok&#039;);\n    // indentasi</code></pre>");
});

it('memakai urutan isian bila diisi', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.materi-tutorial.store'), dataMateriValid(['urutan_ke' => 7]))
        ->assertSessionHasNoErrors();

    expect(MateriTutorial::sole()->urutan_ke)->toBe(7);
});

it('memvalidasi isian materi', function (Closure $data, string $kolom) {
    $this->actingAs($this->admin)
        ->from(route('admin.materi-tutorial.create'))
        ->post(route('admin.materi-tutorial.store'), dataMateriValid($data()))
        ->assertRedirect(route('admin.materi-tutorial.create'))
        ->assertSessionHasErrors($kolom);

    expect(MateriTutorial::count())->toBe(0);
})->with([
    'tutorial kosong' => [fn () => ['id_tutorial' => ''], 'id_tutorial'],
    'tutorial tidak ada' => [fn () => ['id_tutorial' => 999999], 'id_tutorial'],
    'tutorial sudah dihapus' => [fn () => ['id_tutorial' => tap(Tutorial::factory()->create())->delete()->id_tutorial], 'id_tutorial'],
    'judul kosong' => [fn () => ['judul' => ''], 'judul'],
    'judul terlalu panjang' => [fn () => ['judul' => str_repeat('a', 201)], 'judul'],
    'isi kosong' => [fn () => ['deskripsi' => '<pre><code> </code></pre>'], 'deskripsi'],
    'isi lebih dari 30.000 karakter terlihat' => [fn () => ['deskripsi' => '<p>'.str_repeat('a', 30001).'</p>'], 'deskripsi'],
    'urutan bukan angka' => [fn () => ['urutan_ke' => 'satu'], 'urutan_ke'],
    'urutan negatif' => [fn () => ['urutan_ke' => -1], 'urutan_ke'],
    'urutan melebihi batas' => [fn () => ['urutan_ke' => 1000], 'urutan_ke'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.materi-tutorial.store'), dataMateriValid(['id_tutorial' => '', 'deskripsi' => '']))
        ->assertSessionHasErrors([
            'id_tutorial' => 'Kolom tutorial wajib diisi.',
            'deskripsi' => 'Kolom isi materi wajib diisi.',
        ]);
});

it('mengunci pilihan tutorial di form ubah', function () {
    $materi = MateriTutorial::factory()->for($this->tutorial)->create(['judul' => 'Routing', 'urutan_ke' => 3]);

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.materi-tutorial.edit', $materi))
        ->assertOk()
        ->assertSee('value="Routing"', false)
        ->assertSee('Tutorial tidak bisa diganti setelah materi dibuat');

    expect($respons->getContent())
        ->toMatch('#name="urutan_ke"\s+value="3"#')
        // Isian nonaktif tidak ikut terkirim.
        ->toMatch('#<select id="id_tutorial" name="id_tutorial"[^>]*\sdisabled#');
});

it('memperbarui materi tanpa memindahkannya ke tutorial lain', function () {
    $materi = MateriTutorial::factory()->for($this->tutorial)->create(['urutan_ke' => 1]);
    $lain = Tutorial::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.materi-tutorial.update', $materi), dataMateriValid([
            'id_tutorial' => $lain->id_tutorial, 'judul' => 'Judul Baru', 'urutan_ke' => 2,
        ]))
        ->assertRedirect(route('admin.materi-tutorial.index', ['tutorial' => $this->tutorial->id_tutorial]))
        ->assertSessionHasNoErrors();

    expect($materi->refresh()->id_tutorial)->toBe($this->tutorial->id_tutorial)
        ->and($materi->judul)->toBe('Judul Baru')
        ->and($materi->urutan_ke)->toBe(2);
});

it('mewajibkan urutan saat materi diubah', function () {
    $materi = MateriTutorial::factory()->for($this->tutorial)->create();

    $this->actingAs($this->admin)
        ->put(route('admin.materi-tutorial.update', $materi), dataMateriValid(['urutan_ke' => '']))
        ->assertSessionHasErrors(['urutan_ke' => 'Kolom urutan wajib diisi.']);
});

it('menghapus materi secara soft delete', function () {
    $materi = MateriTutorial::factory()->for($this->tutorial)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.materi-tutorial.destroy', $materi))
        ->assertRedirect(route('admin.materi-tutorial.index', ['tutorial' => $this->tutorial->id_tutorial]))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($materi);
});
