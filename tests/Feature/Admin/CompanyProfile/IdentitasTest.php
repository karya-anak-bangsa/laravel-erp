<?php

use App\Models\CompanyProfile\Identitas;
use App\Models\Pengguna;
use Database\Seeders\IdentitasSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

function dataIdentitasValid(array $timpa = []): array
{
    return [
        'nama_perusahaan' => 'PT. Teknologi Karya Anak Bangsa',
        'judul_website' => 'Jasa Website & Pelatihan IT',
        'alamat_website' => 'https://karyaanakbangsa.co.id',
        'meta_deskripsi' => 'Jasa pembuatan website, mobile apps, dan pelatihan IT.',
        'meta_keyword' => 'website, mobile apps, pelatihan IT',
        'email' => 'info@karyaanakbangsa.co.id',
        'telepon' => '+62 812-3456-7890',
        'alamat' => 'Jl. Merdeka No. 1, Jakarta',
        'link_youtube' => 'https://www.youtube.com/@karyaanakbangsa',
        'link_instagram' => 'https://www.instagram.com/karyaanakbangsa',
        'link_whatsapp' => 'https://wa.me/6281234567890',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    Identitas::factory()->create();

    $this->get(route('admin.identitas.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.identitas.update'), dataIdentitasValid())->assertRedirect(route('login'));
});

it('menampilkan form identitas berisi data tersimpan', function () {
    $identitas = Identitas::factory()->create(['nama_perusahaan' => 'PT Uji Identitas']);

    $this->actingAs($this->admin)
        ->get(route('admin.identitas.edit'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSee('Informasi Website')
        ->assertSee('value="PT Uji Identitas"', false)
        ->assertSee(Storage::disk('public')->url($identitas->logo_website))
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('data-confirm-variant="success"', false);
});

it('menandai menu identitas sebagai menu aktif', function () {
    Identitas::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.identitas.edit'))
        ->assertSee('class="nav-link active" href="'.route('admin.identitas.edit').'"', false);
});

it('menampilkan 404 bila baris identitas belum dibuat seeder', function () {
    $this->actingAs($this->admin)->get(route('admin.identitas.edit'))->assertNotFound();
});

it('memperbarui identitas tanpa mengganti logo dan favicon', function () {
    $identitas = Identitas::factory()->create();
    $logoLama = $identitas->logo_website;

    $this->actingAs($this->admin)
        ->put(route('admin.identitas.update'), dataIdentitasValid())
        ->assertRedirect(route('admin.identitas.edit'))
        ->assertSessionHas('success');

    $identitas->refresh();
    expect($identitas->judul_website)->toBe('Jasa Website & Pelatihan IT')
        ->and($identitas->telepon)->toBe('+62 812-3456-7890')
        ->and($identitas->logo_website)->toBe($logoLama);
});

it('mengganti logo dan favicon lalu menghapus berkas lama', function () {
    Storage::disk('public')->put('company-profile/identitas/logo-lama.png', 'isi');
    Storage::disk('public')->put('company-profile/identitas/favicon-lama.png', 'isi');
    $identitas = Identitas::factory()->create([
        'logo_website' => 'company-profile/identitas/logo-lama.png',
        'favicon_website' => 'company-profile/identitas/favicon-lama.png',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.identitas.update'), dataIdentitasValid([
            'logo_website' => UploadedFile::fake()->image('logo.png', 400, 120),
            'favicon_website' => UploadedFile::fake()->create('favicon.ico', 4, 'image/x-icon'),
        ]))
        ->assertRedirect(route('admin.identitas.edit'))
        ->assertSessionHasNoErrors();

    $identitas->refresh();
    expect($identitas->logo_website)->toMatch('#^company-profile/identitas/[0-9a-f-]{36}\.png$#')
        ->and($identitas->favicon_website)->toMatch('#^company-profile/identitas/[0-9a-f-]{36}\.ico$#');
    Storage::disk('public')->assertExists([$identitas->logo_website, $identitas->favicon_website]);
    Storage::disk('public')->assertMissing(['company-profile/identitas/logo-lama.png', 'company-profile/identitas/favicon-lama.png']);
});

it('tidak lagi menyediakan link Google Maps karena peta frontend memakai Leaflet', function () {
    $identitas = Identitas::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.identitas.edit'))
        ->assertOk()
        ->assertDontSee('name="link_gmap"', false)
        ->assertDontSee('Google Maps');

    // Kiriman lama yang masih membawa link_gmap diabaikan, bukan menyebabkan galat SQL.
    $this->actingAs($this->admin)
        ->put(route('admin.identitas.update'), dataIdentitasValid(['link_gmap' => 'https://www.google.com/maps/embed?pb=x']))
        ->assertSessionHasNoErrors();

    expect(Schema::hasColumn('tb_identitas', 'link_gmap'))->toBeFalse()
        ->and($identitas->refresh()->getAttributes())->not->toHaveKey('link_gmap');
});

it('memvalidasi isian identitas', function (array $data, string $kolom) {
    Identitas::factory()->create();

    $this->actingAs($this->admin)
        ->from(route('admin.identitas.edit'))
        ->put(route('admin.identitas.update'), dataIdentitasValid($data))
        ->assertRedirect(route('admin.identitas.edit'))
        ->assertSessionHasErrors($kolom);
})->with([
    'nama perusahaan kosong' => [['nama_perusahaan' => ''], 'nama_perusahaan'],
    'judul website terlalu panjang' => [['judul_website' => str_repeat('a', 151)], 'judul_website'],
    'alamat website bukan URL' => [['alamat_website' => 'karyaanakbangsa'], 'alamat_website'],
    'email tidak valid' => [['email' => 'bukan-email'], 'email'],
    'telepon berisi huruf' => [['telepon' => '0812-ABC'], 'telepon'],
    'alamat kosong' => [['alamat' => ''], 'alamat'],
    'instagram bukan URL' => [['link_instagram' => '@karyaanakbangsa'], 'link_instagram'],
    'logo bukan gambar' => [['logo_website' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')], 'logo_website'],
    'logo lebih dari 2 MB' => [['logo_website' => UploadedFile::fake()->image('logo.png')->size(2049)], 'logo_website'],
    'favicon bertipe svg' => [['favicon_website' => UploadedFile::fake()->create('favicon.svg', 2, 'image/svg+xml')], 'favicon_website'],
    'favicon lebih dari 512 KB' => [['favicon_website' => UploadedFile::fake()->image('favicon.png')->size(513)], 'favicon_website'],
]);

it('menampilkan pesan validasi berbahasa Indonesia', function () {
    Identitas::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.identitas.update'), dataIdentitasValid(['nama_perusahaan' => '', 'telepon' => 'abc']))
        ->assertSessionHasErrors([
            'nama_perusahaan' => 'Kolom nama perusahaan wajib diisi.',
            'telepon' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda hubung, dan awalan +.',
        ]);
});

it('membuat baris identitas awal beserta logo dan favicon dari seeder', function () {
    $this->seed(IdentitasSeeder::class);

    $identitas = Identitas::sole();
    expect($identitas->nama_perusahaan)->toBe('PT. Teknologi Karya Anak Bangsa')
        ->and($identitas->alamat_website)->toBe('https://karyaanakbangsa.co.id');
    Storage::disk('public')->assertExists([$identitas->logo_website, $identitas->favicon_website]);
});

it('menjalankan seeder ulang tanpa menimpa isian admin', function () {
    $this->seed(IdentitasSeeder::class);
    Identitas::query()->update(['email' => 'info@karyaanakbangsa.co.id']);

    $this->seed(IdentitasSeeder::class);

    expect(Identitas::count())->toBe(1)
        ->and(Identitas::first()->email)->toBe('info@karyaanakbangsa.co.id');
});
