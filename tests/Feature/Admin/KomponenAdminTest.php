<?php

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->withViewErrors([]);
    // old() membaca input lama dari sesi milik request; di luar siklus HTTP sesi perlu dipasang manual.
    request()->setLaravelSession(session()->driver());
});

it('merender page-header dengan judul dan tombol aksi', function () {
    $this->blade('<x-admin.page-header title="Akun Kas" pretitle="Kas Perusahaan"><a href="#">Tambah</a></x-admin.page-header>')
        ->assertSee('Kas Perusahaan')
        ->assertSee('<h1 class="page-title">Akun Kas</h1>', false)
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
    $this->withViewErrors(['nama_akun' => 'Kolom nama akun wajib diisi.']);
    session()->flashInput(['nama_akun' => 'Kas Tunai']);

    $this->blade('<x-admin.form-input name="nama_akun" label="Nama Akun" :required="true" />')
        ->assertSee('for="nama_akun"', false)
        ->assertSee('value="Kas Tunai"', false)
        ->assertSee('form-control is-invalid', false)
        ->assertSee('<span class="required">*</span>', false)
        ->assertSee('Kolom nama akun wajib diisi.');
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
        '<x-admin.form-select name="jenis_akun" label="Jenis" :options="$opsi" value="bank" />',
        ['opsi' => ['tunai' => 'Tunai', 'bank' => 'Bank']],
    )
        ->assertSee('<option value="">— Pilih —</option>', false)
        ->assertSee('<option value="bank" selected>Bank</option>', false)
        ->assertSee('<option value="tunai" >Tunai</option>', false);
});

it('merender form-textarea dan form-file', function () {
    $this->blade('<x-admin.form-textarea name="keterangan" label="Keterangan" value="Catatan" />')
        ->assertSee('>Catatan</textarea>', false);

    $this->blade('<x-admin.form-file name="bukti" label="Bukti" accept=".pdf" berkas-saat-ini="/berkas/1" />')
        ->assertSee('type="file"', false)
        ->assertSee('accept=".pdf"', false)
        ->assertSee('Lihat file saat ini');
});

it('merender form-switch dengan hidden input bernilai 0 dan status tercentang', function () {
    $this->blade('<x-admin.form-switch name="status_aktif" label="Status" text="Akun aktif" :checked="true" />')
        ->assertSee('<input type="hidden" name="status_aktif" value="0">', false)
        ->assertSee('name="status_aktif" value="1" checked', false)
        ->assertSee('<span class="switch-label">Akun aktif</span>', false);
});

it('memakai nilai lama pada form-switch setelah validasi gagal', function () {
    session()->flashInput(['status_aktif' => '0']);

    $this->blade('<x-admin.form-switch name="status_aktif" :checked="true" />')
        ->assertDontSee('checked', false);
});

it('merender filter-bar dengan kata kunci dan tombol reset saat filter aktif', function () {
    $this->app->instance('request', Request::create('/admin/uji', 'GET', ['q' => 'bca', 'jenis' => 'bank', 'page' => '2']));

    $this->blade('<x-admin.filter-bar action="/admin/uji" placeholder="Cari akun…"><select name="jenis"></select></x-admin.filter-bar>')
        ->assertSee('method="GET" action="/admin/uji"', false)
        ->assertSee('name="q" value="bca"', false)
        ->assertSee('<select name="jenis"></select>', false)
        ->assertSee('href="/admin/uji" class="btn btn-ghost"', false);
});

it('menyembunyikan tombol reset filter-bar bila tidak ada filter', function () {
    $this->blade('<x-admin.filter-bar action="/admin/uji" />')
        ->assertDontSee('Reset');
});

it('merender delete-button sebagai form DELETE dengan konfirmasi', function () {
    $this->blade('<x-admin.delete-button action="/admin/akun-kas/1" />')
        ->assertSee('action="/admin/akun-kas/1"', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('data-confirm=', false)
        ->assertSee('btn btn-sm btn-danger', false)
        ->assertSee('Hapus');
});

it('merender empty-state dan alert', function () {
    $this->blade('<x-admin.empty-state title="Belum ada akun kas" description="Tambahkan akun pertama." />')
        ->assertSee('Belum ada akun kas')
        ->assertSee('Tambahkan akun pertama.');

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
        ->withSession(['success' => 'Akun kas berhasil disimpan.'])
        ->get(route('admin.dashboard'))
        ->assertSee('id="flash-toast"', false)
        ->assertSee('Akun kas berhasil disimpan.');
});

it('tidak merender data toast bila tidak ada flash message', function () {
    $this->actingAs(Pengguna::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertDontSee('id="flash-toast"', false);
});
