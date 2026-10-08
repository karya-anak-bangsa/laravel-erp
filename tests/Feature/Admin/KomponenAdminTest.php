<?php

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
        ->assertSee('Terapkan')
        ->assertSee('href="/admin/uji" class="btn btn-ghost"', false);
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
