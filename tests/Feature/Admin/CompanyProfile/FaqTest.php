<?php

use App\Models\CompanyProfile\Faq;
use App\Models\Pengguna;
use Database\Seeders\FaqSeeder;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

function dataFaqValid(array $timpa = []): array
{
    return [
        'pertanyaan' => 'Berapa lama proses pembuatan website?',
        'jawaban' => '<p>Bergantung pada jumlah halaman dan fitur.</p>',
        'urutan_ke' => 1,
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $faq = Faq::factory()->create();

    $this->get(route('admin.faq.index'))->assertRedirect(route('login'));
    $this->get(route('admin.faq.create'))->assertRedirect(route('login'));
    $this->post(route('admin.faq.store'), dataFaqValid())->assertRedirect(route('login'));
    $this->get(route('admin.faq.edit', $faq))->assertRedirect(route('login'));
    $this->put(route('admin.faq.update', $faq), dataFaqValid())->assertRedirect(route('login'));
    $this->delete(route('admin.faq.destroy', $faq))->assertRedirect(route('login'));
});

it('menampilkan daftar FAQ sesuai urutan lalu pertanyaan', function () {
    Faq::factory()->create(['pertanyaan' => 'Siapa yang boleh ikut bootcamp?', 'urutan_ke' => 2]);
    Faq::factory()->create(['pertanyaan' => 'Bagaimana cara memesan website?', 'urutan_ke' => 1]);
    Faq::factory()->create(['pertanyaan' => 'Apa isi sertifikasi IT?', 'urutan_ke' => 2]);

    $this->actingAs($this->admin)
        ->get(route('admin.faq.index'))
        ->assertOk()
        ->assertSeeInOrder(['Bagaimana cara memesan website?', 'Apa isi sertifikasi IT?', 'Siapa yang boleh ikut bootcamp?'])
        ->assertSee('class="nav-link active" href="'.route('admin.faq.index').'"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    Faq::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.faq.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">FAQ</div>',
            'href="'.route('admin.faq.create').'"',
            '<table class="table">',
        ], false);
});

it('menampilkan tombol ikon lihat, ubah, dan hapus beserta rincian untuk modal', function () {
    $faq = Faq::factory()->create([
        'pertanyaan' => 'Apakah pelatihan bisa daring?',
        'jawaban' => '<p>Bisa, <strong>tatap muka</strong> maupun daring.</p>',
        'urutan_ke' => 3,
        'created_at' => '2026-10-09 08:30:00',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.faq.index'))
        ->assertSee('<div class="card-subtitle">Bisa, tatap muka maupun daring.</div>', false)
        ->assertSeeInOrder([
            'aria-label="Lihat"',
            'data-detail-title="Detail FAQ"',
            '<template id="detail-',
            '<td class="konten-html"><p>Bisa, <strong>tatap muka</strong> maupun daring.</p></td>',
            'Urutan ke</th>',
            '09 Oktober 2026, 08:30',
            '</template>',
            'href="'.route('admin.faq.edit', $faq).'"',
            'aria-label="Ubah"',
            'action="'.route('admin.faq.destroy', $faq).'"',
            'aria-label="Hapus"',
        ], false);
});

it('mencari FAQ berdasarkan pertanyaan atau jawaban', function () {
    Faq::factory()->create(['pertanyaan' => 'Apakah pelatihan bisa daring?', 'jawaban' => '<p>Bisa.</p>']);
    Faq::factory()->create(['pertanyaan' => 'Berapa lama pembuatan aplikasi?', 'jawaban' => '<p>Tergantung fitur Android.</p>']);

    $this->actingAs($this->admin)
        ->get(route('admin.faq.index', ['q' => 'android']))
        ->assertSee('Berapa lama pembuatan aplikasi?')
        ->assertDontSee('Apakah pelatihan bisa daring?');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.faq.index'))
        ->assertSee('Belum ada FAQ')
        ->assertSee('Tambah FAQ Pertama');

    Faq::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.faq.index', ['q' => 'tidak-ada']))
        ->assertSee('FAQ tidak ditemukan');
});

it('mengisi urutan bawaan form tambah dengan urutan terakhir ditambah satu', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.faq.create'))
        ->assertOk()
        ->assertSee('name="urutan_ke"', false)
        ->assertSee('value="1"', false);

    Faq::factory()->create(['urutan_ke' => 7]);

    $this->actingAs($this->admin)
        ->get(route('admin.faq.create'))
        ->assertSee('value="8"', false);
});

it('menambah FAQ', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.faq.store'), dataFaqValid())
        ->assertRedirect(route('admin.faq.index'))
        ->assertSessionHas('success');

    $faq = Faq::sole();
    expect($faq->pertanyaan)->toBe('Berapa lama proses pembuatan website?')
        ->and($faq->jawaban)->toBe('<p>Bergantung pada jumlah halaman dan fitur.</p>')
        ->and($faq->urutan_ke)->toBe(1);
});

it('boleh memakai ulang pertanyaan FAQ yang sudah dihapus', function () {
    Faq::factory()->create(['pertanyaan' => 'Berapa lama proses pembuatan website?'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.faq.store'), dataFaqValid())
        ->assertSessionHasNoErrors();
});

it('menampilkan form ubah berisi data tersimpan', function () {
    $faq = Faq::factory()->create(['pertanyaan' => 'Apa isi sertifikasi IT?', 'urutan_ke' => 4]);

    $this->actingAs($this->admin)
        ->get(route('admin.faq.edit', $faq))
        ->assertOk()
        ->assertSee('value="Apa isi sertifikasi IT?"', false)
        ->assertSee('value="4"', false);
});

it('memperbarui FAQ dengan pertanyaan sendiri', function () {
    $faq = Faq::factory()->create(['pertanyaan' => 'Berapa lama proses pembuatan website?']);

    $this->actingAs($this->admin)
        ->put(route('admin.faq.update', $faq), dataFaqValid(['jawaban' => '<p>Sekitar dua minggu.</p>', 'urutan_ke' => 3]))
        ->assertRedirect(route('admin.faq.index'))
        ->assertSessionHasNoErrors();

    expect($faq->refresh()->urutan_ke)->toBe(3)
        ->and($faq->jawaban)->toBe('<p>Sekitar dua minggu.</p>');
});

it('menolak pertanyaan yang sudah dipakai FAQ lain saat mengubah', function () {
    Faq::factory()->create(['pertanyaan' => 'Apa isi sertifikasi IT?']);
    $faq = Faq::factory()->create(['pertanyaan' => 'Siapa yang boleh ikut bootcamp?']);

    $this->actingAs($this->admin)
        ->put(route('admin.faq.update', $faq), dataFaqValid(['pertanyaan' => 'Apa isi sertifikasi IT?']))
        ->assertSessionHasErrors(['pertanyaan' => 'Pertanyaan ini sudah ada di FAQ lain.']);
});

it('memvalidasi isian FAQ', function (array $data, string $kolom) {
    Faq::factory()->create(['pertanyaan' => 'Sudah Ada?']);

    $this->actingAs($this->admin)
        ->from(route('admin.faq.create'))
        ->post(route('admin.faq.store'), dataFaqValid($data))
        ->assertRedirect(route('admin.faq.create'))
        ->assertSessionHasErrors($kolom);

    expect(Faq::count())->toBe(1);
})->with([
    'pertanyaan kosong' => [['pertanyaan' => ''], 'pertanyaan'],
    'pertanyaan terlalu panjang' => [['pertanyaan' => str_repeat('a', 256)], 'pertanyaan'],
    'pertanyaan sudah dipakai' => [['pertanyaan' => 'Sudah Ada?'], 'pertanyaan'],
    'jawaban kosong' => [['jawaban' => ''], 'jawaban'],
    'jawaban terlalu panjang' => [['jawaban' => str_repeat('a', 2001)], 'jawaban'],
    'urutan kosong' => [['urutan_ke' => ''], 'urutan_ke'],
    'urutan bukan angka' => [['urutan_ke' => 'satu'], 'urutan_ke'],
    'urutan negatif' => [['urutan_ke' => -1], 'urutan_ke'],
    'urutan lebih dari 999' => [['urutan_ke' => 1000], 'urutan_ke'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.faq.store'), dataFaqValid(['pertanyaan' => '', 'urutan_ke' => 1000]))
        ->assertSessionHasErrors([
            'pertanyaan' => 'Kolom pertanyaan wajib diisi.',
            'urutan_ke' => 'Kolom urutan tidak boleh lebih besar dari 999.',
        ]);
});

it('menghapus FAQ secara soft delete', function () {
    $faq = Faq::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.faq.destroy', $faq))
        ->assertRedirect(route('admin.faq.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($faq);
});

it('membuat lima FAQ awal dari seeder', function () {
    $this->seed(FaqSeeder::class);

    expect(Faq::count())->toBe(5)
        ->and(Faq::berurutan()->pluck('urutan_ke')->all())->toBe([1, 2, 3, 4, 5])
        ->and(Faq::berurutan()->value('jawaban'))->toStartWith('<p>');
});

it('menjalankan seeder FAQ ulang tanpa menimpa isian admin', function () {
    $this->seed(FaqSeeder::class);
    Faq::query()->where('urutan_ke', 1)->update(['pertanyaan' => 'Bagaimana cara memesan?']);

    $this->seed(FaqSeeder::class);

    expect(Faq::count())->toBe(5)
        ->and(Faq::where('urutan_ke', 1)->value('pertanyaan'))->toBe('Bagaimana cara memesan?');
});
