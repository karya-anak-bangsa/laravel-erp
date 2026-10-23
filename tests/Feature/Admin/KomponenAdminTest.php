<?php

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

enum StatusUjiKomponen: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';
}

beforeEach(function () {
    $this->withViewErrors([]);
    // old() membaca input lama dari sesi milik request; di luar siklus HTTP sesi perlu dipasang manual.
    request()->setLaravelSession(session()->driver());
});

it('merender page-header dengan judul dan tombol aksi', function () {
    $this->blade('<x-admin.page-header title="Kategori Artikel" pretitle="Company Profile"><a href="#">Tambah</a></x-admin.page-header>')
        ->assertSee('Company Profile')
        ->assertSee('<h1 class="page-title">Kategori Artikel</h1>', false)
        ->assertSee('<div class="page-actions"><a href="#">Tambah</a></div>', false);
});

it('merender card dengan judul, slot aksi, dan footer', function () {
    $this->blade('<x-admin.card title="Daftar" :flush="true"><x-slot:aksi>Aksi</x-slot> Isi <x-slot:footer>Kaki</x-slot></x-admin.card>')
        ->assertSee('<div class="card-title">Daftar</div>', false)
        ->assertSee('<div class="card-options">Aksi</div>', false)
        ->assertSee('card-body p-0', false)
        ->assertSee('<div class="card-footer">Kaki</div>', false);
});

it('menampilkan pesan error dan nilai lama pada form-input', function () {
    $this->withViewErrors(['nama_kategori' => 'Kolom nama kategori wajib diisi.']);
    session()->flashInput(['nama_kategori' => 'Teknologi']);

    $this->blade('<x-admin.form-input name="nama_kategori" label="Nama Kategori" :required="true" />')
        ->assertSee('for="nama_kategori"', false)
        ->assertSee('value="Teknologi"', false)
        ->assertSee('form-control is-invalid', false)
        ->assertSee('<span class="required">*</span>Nama Kategori</label>', false)
        ->assertSee('Kolom nama kategori wajib diisi.');
});

it('tidak mengisi ulang nilai pada input password', function () {
    session()->flashInput(['password' => 'rahasia']);

    $this->blade('<x-admin.form-input name="password" type="password" label="Kata Sandi" />')
        ->assertDontSee('rahasia');
});

it('memberi input password tombol tampil kata sandi yang tersembunyi sampai JavaScript berjalan', function () {
    $this->blade('<x-admin.form-input name="password" type="password" label="Kata Sandi" />')
        ->assertSeeInOrder([
            '<div class="isian-sandi">', 'id="password"',
            '<button type="button" class="input-toggle" data-toggle-password="password"', 'hidden>', 'fa-eye',
        ], false);

    $this->blade('<x-admin.form-input name="email" type="email" label="Email" />')
        ->assertDontSee('isian-sandi', false)
        ->assertDontSee('data-toggle-password', false);
});

it('memakai notasi titik untuk error pada nama input array', function () {
    $this->withViewErrors(['cta.0.label' => 'Label wajib diisi.']);

    $this->blade('<x-admin.form-input name="cta[0][label]" label="Label" />')
        ->assertSee('id="cta-0-label"', false)
        ->assertSee('Label wajib diisi.');
});

it('memilih opsi sesuai nilai pada form-select', function () {
    $this->blade(
        '<x-admin.form-select name="jenis_layanan" label="Jenis" :options="$opsi" value="mobile" />',
        ['opsi' => ['website' => 'Website', 'mobile' => 'Mobile Apps']],
    )
        ->assertSee('<option value="">— Pilih —</option>', false)
        ->assertSee('<option value="mobile" selected>Mobile Apps</option>', false)
        ->assertSee('<option value="website" >Website</option>', false);
});

it('mengelompokkan opsi form-select dengan optgroup', function () {
    $this->blade(
        '<x-admin.form-select name="layanan" label="Layanan" :options="$opsi" value="android" />',
        ['opsi' => ['Website' => ['landing' => 'Landing Page'], 'Mobile' => ['android' => 'Android']]],
    )
        ->assertSee('<optgroup label="Website">', false)
        ->assertSee('<optgroup label="Mobile">', false)
        ->assertSee('<option value="android" selected>Android</option>', false)
        ->assertSee('<option value="landing" >Landing Page</option>', false);
});

it('memilih opsi form-select dari nilai enum dan bisa tanpa placeholder', function () {
    $this->blade(
        '<x-admin.form-select name="status" label="Status" :options="$opsi" :value="$nilai" :placeholder="false" />',
        ['opsi' => ['draf' => 'Draf', 'terbit' => 'Terbit'], 'nilai' => StatusUjiKomponen::Terbit],
    )
        ->assertSee('<option value="terbit" selected>Terbit</option>', false)
        ->assertDontSee('— Pilih —');
});

it('merender form-textarea dan form-file', function () {
    $this->blade('<x-admin.form-textarea name="deskripsi" label="Deskripsi" value="Catatan" />')
        ->assertSee('>Catatan</textarea>', false);

    $this->blade('<x-admin.form-file name="gambar" label="Gambar" accept=".webp" berkas-saat-ini="/berkas/1" />')
        ->assertSee('type="file"', false)
        ->assertSee('accept=".webp"', false)
        ->assertSee('Lihat file saat ini');
});

it('merender form-switch dengan hidden input bernilai 0 dan status tercentang', function () {
    $this->blade('<x-admin.form-switch name="status_aktif" label="Status" text="Tampilkan di website" :checked="true" />')
        ->assertSee('<input type="hidden" name="status_aktif" value="0">', false)
        ->assertSee('name="status_aktif" value="1" checked', false)
        ->assertSee('<span class="switch-label">Tampilkan di website</span>', false);
});

it('memakai nilai lama pada form-switch setelah validasi gagal', function () {
    session()->flashInput(['status_aktif' => '0']);

    $this->blade('<x-admin.form-switch name="status_aktif" :checked="true" />')
        ->assertDontSee('checked', false);
});

it('merender filter-bar dengan kata kunci dan tombol reset saat filter aktif', function () {
    $this->app->instance('request', Request::create('/admin/uji', 'GET', ['q' => 'laravel', 'kategori' => 'web', 'page' => '2']));

    $this->blade('<x-admin.filter-bar action="/admin/uji" placeholder="Cari artikel…"><select name="kategori"></select></x-admin.filter-bar>')
        ->assertSee('method="GET" action="/admin/uji"', false)
        ->assertSee('name="q" value="laravel"', false)
        ->assertSee('<select name="kategori"></select>', false)
        // Terapkan bergaya tombol Batal, Reset bergaya tombol Hapus, dalam satu wadah agar sama tinggi & lebar.
        ->assertSeeInOrder([
            '<div class="aksi-filter">',
            '<button type="submit" class="btn btn-secondary">',
            'fa-magnifying-glass',
            'Terapkan',
            '<a href="/admin/uji" class="btn btn-danger">',
            'fa-clock-rotate-left',
            'Reset',
        ], false);
});

it('merender tombol-tambah dengan label yang disembunyikan CSS di ponsel dan nama di title & aria-label', function () {
    $this->blade('<x-admin.tombol-tambah href="/admin/uji/create" />')
        ->assertSee('<a href="/admin/uji/create" class="btn btn-success tombol-tambah" title="Tambah Data"', false)
        ->assertSee('aria-label="Tambah Data"', false)
        ->assertSee('<span class="label-tombol">Tambah Data</span>', false);
});

it('menyembunyikan tombol reset filter-bar bila tidak ada filter', function () {
    $this->blade('<x-admin.filter-bar action="/admin/uji" />')
        ->assertSee('placeholder="Cari Data"', false)
        ->assertDontSee('Reset');
});

it('merender filter-bar tanpa kotak pencarian bila cari bernilai false', function () {
    $this->blade('<x-admin.filter-bar action="/admin/uji" :cari="false"><select name="tahun"></select></x-admin.filter-bar>')
        ->assertDontSee('name="q"', false)
        ->assertSee('<select name="tahun"></select>', false)
        ->assertSee('Terapkan');
});

it('merender delete-button sebagai form DELETE dengan konfirmasi', function () {
    $this->blade('<x-admin.delete-button action="/admin/kategori-artikel/1" />')
        ->assertSee('action="/admin/kategori-artikel/1"', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('data-confirm=', false)
        ->assertSee('btn btn-sm btn-danger', false)
        ->assertSee('Hapus');
});

it('merender detail-button dengan template rincian yang ter-escape', function () {
    $html = (string) $this->blade(
        '<x-admin.detail-button title="Detail Portofolio">{{ $isi }}</x-admin.detail-button>',
        ['isi' => '<script>alert(1)</script>'],
    );

    preg_match('/data-detail="(detail-[A-Za-z0-9]+)"/', $html, $cocok);

    expect($cocok)->not->toBeEmpty()
        ->and($html)->toContain('type="button" class="btn btn-sm btn-info"')
        ->toContain('data-detail-title="Detail Portofolio"')
        ->toContain('fa-eye')
        ->toContain('Lihat')
        ->toContain('<template id="'.$cocok[1].'">')
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>alert(1)</script>');
});

it('merender detail-button dan delete-button sebagai tombol ikon tanpa teks', function () {
    $this->blade('<x-admin.detail-button :ikon-saja="true">Isi</x-admin.detail-button>')
        ->assertSee('class="btn btn-info btn-ikon"', false)
        ->assertSee('title="Lihat"', false)
        ->assertSee('aria-label="Lihat"', false)
        ->assertDontSeeText('Lihat');

    $this->blade('<x-admin.delete-button action="/admin/uji/1" :ikon-saja="true" />')
        ->assertSee('class="btn btn-danger btn-ikon"', false)
        ->assertSee('title="Hapus"', false)
        ->assertSee('aria-label="Hapus"', false)
        ->assertDontSeeText('Hapus');
});

it('merender form tambah dengan konfirmasi simpan bawaan', function () {
    $this->blade('<x-admin.form action="/admin/uji" title="Simpan uji baru?">Isi</x-admin.form>')
        ->assertSee('<form method="POST" action="/admin/uji"', false)
        ->assertSee('novalidate', false)
        ->assertSee('name="_token"', false)
        ->assertDontSee('name="_method"', false)
        ->assertDontSee('enctype', false)
        ->assertSeeInOrder([
            'data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan uji baru?"',
            'data-confirm-label="Ya, simpan" data-confirm-variant="success">',
            'Isi',
            '</form>',
        ], false);
});

it('merender form ubah ber-method PUT, berkas, atribut tambahan, dan pesan yang di-escape sekali', function () {
    $this->blade(
        '<x-admin.form action="/admin/uji/1" method="PUT" :berkas="true" :message="$pesan" data-form-uji>Isi</x-admin.form>',
        ['pesan' => 'Perubahan “A & B” akan disimpan.'],
    )
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('data-form-uji="data-form-uji"', false)
        ->assertSee('name="_method" value="PUT"', false)
        ->assertSee('data-confirm="Perubahan “A &amp; B” akan disimpan." data-confirm-title="Simpan perubahan?"', false);
});

it('merender form-aksi dengan tombol Simpan dan Batal ke alamat kembali', function () {
    $this->blade('<x-admin.form-aksi batal="/admin/uji" />')
        ->assertSeeInOrder([
            '<div class="form-actions">',
            '<button type="submit" class="btn btn-success">', 'fa-floppy-disk', 'Simpan',
            '<a href="/admin/uji" class="btn btn-secondary">', 'fa-rotate-left', 'Batal',
        ], false);
});

it('merender tabel dalam pembungkus gulir dan sel aksi tabel', function () {
    $this->blade('<x-admin.tabel><tbody><tr><td>Isi</td><x-admin.aksi-tabel>Tombol</x-admin.aksi-tabel></tr></tbody></x-admin.tabel>')
        ->assertSeeInOrder([
            '<div class="table-responsive">', '<table class="table">', '<td>Isi</td>',
            '<td class="kolom-aksi">', '<div class="aksi-tabel">', 'Tombol',
        ], false);
});

it('merender edit-button sebagai tautan ikon atau tombol nonaktif beralasan', function () {
    $this->blade('<x-admin.edit-button href="/admin/uji/1/edit" />')
        ->assertSee('<a href="/admin/uji/1/edit" class="btn btn-warning btn-ikon" title="Ubah" aria-label="Ubah"', false)
        ->assertSee('fa-pen-to-square', false)
        ->assertDontSeeText('Ubah');

    $this->blade('<x-admin.edit-button href="/admin/uji/1/edit" terkunci="akun Anda diubah lewat Profil Saya" />')
        ->assertSee('<button type="button" class="btn btn-warning btn-ikon" disabled', false)
        ->assertSee('title="Akun Anda diubah lewat Profil Saya" aria-label="Ubah (akun Anda diubah lewat Profil Saya)"', false)
        ->assertDontSee('href="/admin/uji/1/edit"', false);
});

it('menutup tabel-detail dengan baris Dibuat dan Diperbarui hanya bila ada model', function () {
    $model = (object) ['created_at' => Carbon::parse('2026-10-20 10:00'), 'updated_at' => Carbon::parse('2026-10-21 08:30')];

    $this->blade('<x-admin.tabel-detail :model="$model"><tr><th scope="row">Judul</th><td>Uji</td></tr></x-admin.tabel-detail>', ['model' => $model])
        ->assertSeeInOrder([
            '<div class="tabel-detail">', '<table class="table tabel-informasi">', '<tbody>',
            '<th scope="row">Judul</th>',
            '<th scope="row">Dibuat</th>', '<td>20 Oktober 2026, 10:00</td>',
            '<th scope="row">Diperbarui</th>', '<td>21 Oktober 2026, 08:30</td>',
        ], false);

    $this->blade('<x-admin.tabel-detail><tr><th scope="row">Waktu</th><td>Uji</td></tr></x-admin.tabel-detail>')
        ->assertSee('<th scope="row">Waktu</th>', false)
        ->assertDontSee('Dibuat')
        ->assertDontSee('Diperbarui');
});

it('merender empty-state dan alert', function () {
    $this->blade('<x-admin.empty-state title="Belum ada kategori artikel" description="Tambahkan kategori pertama." />')
        ->assertSee('Belum ada kategori artikel')
        ->assertSee('Tambahkan kategori pertama.');

    $this->blade('<x-admin.alert variant="success">Berhasil disimpan.</x-admin.alert>')
        ->assertSee('alert alert-success', false)
        ->assertSee('Berhasil disimpan.');
});

it('merender paginasi dengan info jumlah data dan tautan halaman', function () {
    $paginator = new LengthAwarePaginator(range(1, 15), 40, 15, 2, ['path' => '/admin/uji']);

    $this->blade('<x-admin.pagination :paginator="$paginator" />', ['paginator' => $paginator])
        ->assertSee('Menampilkan 16–30 dari 40 data')
        ->assertSee('<span class="page-link active" aria-current="page">2</span>', false)
        ->assertSee('/admin/uji?page=3', false);
});

it('menampilkan flash message sebagai data toast', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->withSession(['success' => 'Kategori artikel berhasil disimpan.'])
        ->get(route('admin.dashboard'))
        ->assertSee('id="flash-toast"', false)
        ->assertSee('Kategori artikel berhasil disimpan.');
});

it('tidak merender data toast bila tidak ada flash message', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertDontSee('id="flash-toast"', false);
});
