<?php

use App\Models\CompanyProfile\Layanan;
use App\Models\Pengguna;
use Database\Seeders\LayananSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

function dataLayananValid(array $timpa = []): array
{
    return [
        'judul' => 'Pembuatan Website',
        'deskripsi' => 'Website company profile dan toko online.',
        'keterangan' => 'Mulai dari desain hingga pemasangan di server.',
        'gambar' => UploadedFile::fake()->image('layanan.webp', 800, 600),
        'urutan_ke' => 1,
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $layanan = Layanan::factory()->create();

    $this->get(route('admin.layanan.index'))->assertRedirect(route('login'));
    $this->get(route('admin.layanan.create'))->assertRedirect(route('login'));
    $this->post(route('admin.layanan.store'), dataLayananValid())->assertRedirect(route('login'));
    $this->get(route('admin.layanan.edit', $layanan))->assertRedirect(route('login'));
    $this->put(route('admin.layanan.update', $layanan), dataLayananValid())->assertRedirect(route('login'));
    $this->delete(route('admin.layanan.destroy', $layanan))->assertRedirect(route('login'));
});

it('menampilkan daftar layanan sesuai urutan lalu judul', function () {
    Layanan::factory()->create(['judul' => 'Sertifikasi IT', 'urutan_ke' => 2]);
    Layanan::factory()->create(['judul' => 'Pembuatan Website', 'urutan_ke' => 1]);
    Layanan::factory()->create(['judul' => 'Bootcamp Mahasiswa', 'urutan_ke' => 2]);

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index'))
        ->assertOk()
        ->assertSeeInOrder(['Pembuatan Website', 'Bootcamp Mahasiswa', 'Sertifikasi IT'])
        ->assertSee('class="nav-link active" href="'.route('admin.layanan.index').'"', false);
});

it('menyusun halaman daftar sesuai standar admin', function () {
    Layanan::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Layanan</div>',
            'href="'.route('admin.layanan.create').'"',
            '<table class="table">',
        ], false);
});

it('menampilkan tombol ikon lihat, ubah, dan hapus beserta rincian untuk modal', function () {
    $layanan = Layanan::factory()->create([
        'judul' => 'Pelatihan IT',
        'deskripsi' => 'Kelas pemrograman.',
        'keterangan' => null,
        'urutan_ke' => 3,
        'created_at' => '2026-10-09 08:30:00',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index'))
        ->assertSeeInOrder([
            'aria-label="Lihat"',
            'data-detail-title="Detail Layanan"',
            '<template id="detail-',
            'Kelas pemrograman.',
            'Keterangan</th>',
            '—',
            'Urutan ke</th>',
            '09 Oktober 2026, 08:30',
            Storage::disk('public')->url($layanan->gambar),
            '</template>',
            'href="'.route('admin.layanan.edit', $layanan).'"',
            'aria-label="Ubah"',
            'action="'.route('admin.layanan.destroy', $layanan).'"',
            'aria-label="Hapus"',
        ], false);
});

it('mencari layanan berdasarkan judul atau deskripsi', function () {
    Layanan::factory()->create(['judul' => 'Pelatihan IT', 'deskripsi' => 'Kelas pemrograman.']);
    Layanan::factory()->create(['judul' => 'Mobile Apps', 'deskripsi' => 'Aplikasi Android.']);

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index', ['q' => 'android']))
        ->assertSee('Mobile Apps')
        ->assertDontSee('Pelatihan IT');
});

it('membedakan empty state data kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index'))
        ->assertSee('Belum ada layanan')
        ->assertSee('Tambah Layanan Pertama');

    Layanan::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.index', ['q' => 'tidak-ada']))
        ->assertSee('Layanan tidak ditemukan');
});

it('mengisi urutan bawaan form tambah dengan urutan terakhir ditambah satu', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.layanan.create'))
        ->assertOk()
        ->assertSee('name="urutan_ke"', false)
        ->assertSee('value="1"', false);

    Layanan::factory()->create(['urutan_ke' => 7]);

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.create'))
        ->assertSee('value="8"', false);
});

it('menambah layanan beserta gambarnya', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.layanan.store'), dataLayananValid())
        ->assertRedirect(route('admin.layanan.index'))
        ->assertSessionHas('success');

    $layanan = Layanan::sole();
    expect($layanan->judul)->toBe('Pembuatan Website')
        ->and($layanan->urutan_ke)->toBe(1)
        ->and($layanan->gambar)->toMatch('#^company-profile/layanan/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($layanan->gambar);
});

it('boleh memakai ulang judul layanan yang sudah dihapus', function () {
    Layanan::factory()->create(['judul' => 'Pembuatan Website'])->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.layanan.store'), dataLayananValid())
        ->assertSessionHasNoErrors();
});

it('menampilkan form ubah berisi data tersimpan', function () {
    $layanan = Layanan::factory()->create(['judul' => 'Sertifikasi IT', 'urutan_ke' => 4]);

    $this->actingAs($this->admin)
        ->get(route('admin.layanan.edit', $layanan))
        ->assertOk()
        ->assertSee('value="Sertifikasi IT"', false)
        ->assertSee('value="4"', false)
        ->assertSee(Storage::disk('public')->url($layanan->gambar));
});

it('memperbarui layanan dengan judul sendiri tanpa mengganti gambar', function () {
    $layanan = Layanan::factory()->create(['judul' => 'Pembuatan Website']);
    $gambarLama = $layanan->gambar;

    $this->actingAs($this->admin)
        ->put(route('admin.layanan.update', $layanan), dataLayananValid(['gambar' => null, 'urutan_ke' => 3]))
        ->assertRedirect(route('admin.layanan.index'))
        ->assertSessionHasNoErrors();

    expect($layanan->refresh()->urutan_ke)->toBe(3)
        ->and($layanan->gambar)->toBe($gambarLama);
});

it('mengganti gambar layanan dan menghapus gambar lama', function () {
    Storage::disk('public')->put('company-profile/layanan/lama.webp', 'isi');
    $layanan = Layanan::factory()->create(['gambar' => 'company-profile/layanan/lama.webp']);

    $this->actingAs($this->admin)
        ->put(route('admin.layanan.update', $layanan), dataLayananValid(['gambar' => UploadedFile::fake()->image('baru.png')]))
        ->assertSessionHasNoErrors();

    expect($layanan->refresh()->gambar)->toMatch('#^company-profile/layanan/[0-9a-f-]{36}\.png$#');
    Storage::disk('public')->assertMissing('company-profile/layanan/lama.webp');
});

it('menolak judul yang sudah dipakai layanan lain saat mengubah', function () {
    Layanan::factory()->create(['judul' => 'Pelatihan IT']);
    $layanan = Layanan::factory()->create(['judul' => 'Mobile Apps']);

    $this->actingAs($this->admin)
        ->put(route('admin.layanan.update', $layanan), dataLayananValid(['judul' => 'Pelatihan IT', 'gambar' => null]))
        ->assertSessionHasErrors(['judul' => 'Judul layanan sudah dipakai layanan lain.']);
});

it('memvalidasi isian layanan', function (array $data, string $kolom) {
    Layanan::factory()->create(['judul' => 'Sudah Ada']);

    $this->actingAs($this->admin)
        ->from(route('admin.layanan.create'))
        ->post(route('admin.layanan.store'), dataLayananValid($data))
        ->assertRedirect(route('admin.layanan.create'))
        ->assertSessionHasErrors($kolom);

    expect(Layanan::count())->toBe(1);
})->with([
    'judul kosong' => [['judul' => ''], 'judul'],
    'judul terlalu panjang' => [['judul' => str_repeat('a', 151)], 'judul'],
    'judul sudah dipakai' => [['judul' => 'Sudah Ada'], 'judul'],
    'deskripsi kosong' => [['deskripsi' => ''], 'deskripsi'],
    'deskripsi terlalu panjang' => [['deskripsi' => str_repeat('a', 1001)], 'deskripsi'],
    'keterangan terlalu panjang' => [['keterangan' => str_repeat('a', 5001)], 'keterangan'],
    'gambar kosong saat tambah' => [['gambar' => null], 'gambar'],
    'gambar bukan gambar' => [['gambar' => UploadedFile::fake()->create('layanan.pdf', 10, 'application/pdf')], 'gambar'],
    'gambar lebih dari 2 MB' => [['gambar' => UploadedFile::fake()->image('layanan.png')->size(2049)], 'gambar'],
    'urutan kosong' => [['urutan_ke' => ''], 'urutan_ke'],
    'urutan bukan angka' => [['urutan_ke' => 'satu'], 'urutan_ke'],
    'urutan negatif' => [['urutan_ke' => -1], 'urutan_ke'],
    'urutan lebih dari 999' => [['urutan_ke' => 1000], 'urutan_ke'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.layanan.store'), dataLayananValid(['judul' => '', 'urutan_ke' => 1000]))
        ->assertSessionHasErrors([
            'judul' => 'Kolom judul wajib diisi.',
            'urutan_ke' => 'Kolom urutan tidak boleh lebih besar dari 999.',
        ]);
});

it('menghapus layanan secara soft delete tanpa menghapus gambarnya', function () {
    Storage::disk('public')->put('company-profile/layanan/tetap.webp', 'isi');
    $layanan = Layanan::factory()->create(['gambar' => 'company-profile/layanan/tetap.webp']);

    $this->actingAs($this->admin)
        ->delete(route('admin.layanan.destroy', $layanan))
        ->assertRedirect(route('admin.layanan.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted($layanan);
    Storage::disk('public')->assertExists('company-profile/layanan/tetap.webp');
});

it('membuat lima layanan perusahaan beserta gambarnya dari seeder', function () {
    $this->seed(LayananSeeder::class);

    expect(Layanan::berurutan()->pluck('judul')->all())->toBe([
        'Pembuatan Website', 'Pembuatan Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp Mahasiswa',
    ]);
    Layanan::all()->each(fn (Layanan $layanan) => Storage::disk('public')->assertExists($layanan->gambar));
});

it('menjalankan seeder layanan ulang tanpa menimpa isian admin', function () {
    $this->seed(LayananSeeder::class);
    Layanan::query()->where('urutan_ke', 1)->update(['judul' => 'Website Profesional']);

    $this->seed(LayananSeeder::class);

    expect(Layanan::count())->toBe(5)
        ->and(Layanan::where('urutan_ke', 1)->value('judul'))->toBe('Website Profesional');
});
