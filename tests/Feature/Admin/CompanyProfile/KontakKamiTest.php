<?php

use App\Models\CompanyProfile\KontakKami;
use App\Models\Pengguna;

beforeEach(function () {
    $this->admin = Pengguna::factory()->create();
});

it('mengarahkan tamu ke halaman login', function () {
    $pesan = KontakKami::factory()->create();

    $this->get(route('admin.kontak-kami.index'))->assertRedirect(route('login'));
    $this->patch(route('admin.kontak-kami.status-baca', $pesan), ['status_baca' => 1])->assertRedirect(route('login'));
    $this->delete(route('admin.kontak-kami.destroy', $pesan))->assertRedirect(route('login'));

    expect($pesan->refresh()->trashed())->toBeFalse();
});

it('tidak menyediakan tambah dan ubah pesan dari panel admin', function () {
    expect(Route::has('admin.kontak-kami.create'))->toBeFalse()
        ->and(Route::has('admin.kontak-kami.store'))->toBeFalse()
        ->and(Route::has('admin.kontak-kami.edit'))->toBeFalse()
        ->and(Route::has('admin.kontak-kami.update'))->toBeFalse();
});

it('menampilkan pesan terbaru lebih dulu', function () {
    KontakKami::factory()->create(['subjek' => 'Pesan lama', 'tanggal' => '2026-10-01 09:00:00']);
    KontakKami::factory()->create(['subjek' => 'Pesan terbaru', 'tanggal' => '2026-10-09 09:00:00']);
    KontakKami::factory()->create(['subjek' => 'Pesan tengah', 'tanggal' => '2026-10-05 09:00:00']);

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index'))
        ->assertOk()
        ->assertSeeInOrder(['Pesan terbaru', 'Pesan tengah', 'Pesan lama'])
        ->assertSee('class="nav-link active" href="'.route('admin.kontak-kami.index').'"', false);
});

it('menyusun halaman daftar sesuai standar admin tanpa tombol tambah', function () {
    KontakKami::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index'))
        ->assertSee('<h1 class="page-title">Company Profile</h1>', false)
        ->assertSeeInOrder([
            'class="card kartu-filter"',
            '<div class="card-title">Kontak Kami</div>',
            '<table class="table tabel-kotak-masuk">',
        ], false)
        ->assertDontSee('Tambah');
});

it('membedakan pesan belum dibaca dan sudah dibaca di daftar', function () {
    $baru = KontakKami::factory()->belumDibaca()->create(['tanggal' => now()]);
    $lama = KontakKami::factory()->dibaca()->create(['tanggal' => now()->subDay()]);

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index'))
        ->assertSeeInOrder([
            '<tr class="belum-dibaca">',
            'status-green" data-status-baca>Belum dibaca',
            'data-tandai-baca="'.route('admin.kontak-kami.status-baca', $baru).'"',
            'name="status_baca" value="1"',
            'aria-label="Tandai dibaca"',
            '<tr class="">',
            'status-abu" data-status-baca>Dibaca',
            'name="status_baca" value="0"',
            'aria-label="Tandai belum dibaca"',
        ], false)
        // Pesan yang sudah dibaca tidak perlu ditandai lagi saat modal dibuka.
        ->assertDontSee('data-tandai-baca="'.route('admin.kontak-kami.status-baca', $lama).'"', false);
});

it('menampilkan rincian pesan untuk modal dengan isi yang di-escape', function () {
    KontakKami::factory()->create([
        'nama' => 'Budi Santoso',
        'email' => 'budi@contoh.id',
        'subjek' => 'Penawaran website',
        'pesan' => "Halo <script>alert(1)</script>\nBaris kedua",
        'tanggal' => '2026-10-09 08:30:00',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index'))
        ->assertSeeInOrder([
            'data-detail-title="Detail Pesan"',
            '<template id="detail-',
            'Budi Santoso',
            'href="mailto:budi@contoh.id?subject=Re%3A%20Penawaran%20website"',
            '<td class="teks-pesan-lengkap">Halo &lt;script&gt;alert(1)&lt;/script&gt;'."\nBaris kedua</td>",
            '09 Oktober 2026, 08:30',
            '</template>',
        ], false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('mencari pesan berdasarkan nama, email, subjek, atau isi pesan', function (string $kata) {
    KontakKami::factory()->create(['nama' => 'Budi Santoso', 'email' => 'budi@sekolah.id', 'subjek' => 'Website sekolah', 'pesan' => 'Butuh halaman PPDB.']);
    KontakKami::factory()->create(['nama' => 'Siti Aminah', 'email' => 'siti@kantor.id', 'subjek' => 'Pelatihan', 'pesan' => 'Kelas Laravel.']);

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index', ['q' => $kata]))
        ->assertSee('Budi Santoso')
        ->assertDontSee('Siti Aminah');
})->with(['budi', 'sekolah.id', 'website sekolah', 'ppdb']);

it('menyaring pesan berdasarkan status baca', function () {
    KontakKami::factory()->belumDibaca()->create(['subjek' => 'Pesan baru masuk']);
    KontakKami::factory()->dibaca()->create(['subjek' => 'Pesan sudah dibuka']);

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index', ['status' => 'belum-dibaca']))
        ->assertSee('Pesan baru masuk')
        ->assertDontSee('Pesan sudah dibuka');

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index', ['status' => 'dibaca']))
        ->assertSee('Pesan sudah dibuka')
        ->assertDontSee('Pesan baru masuk');
});

it('membedakan empty state kotak masuk kosong dan hasil pencarian kosong', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index'))
        ->assertSee('Belum ada pesan masuk');

    KontakKami::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.kontak-kami.index', ['status' => 'dibaca', 'q' => 'tidak-ada']))
        ->assertSee('Pesan tidak ditemukan');
});

it('menandai pesan dibaca dan belum dibaca lalu kembali ke halaman asal', function () {
    $pesan = KontakKami::factory()->belumDibaca()->create();
    $asal = route('admin.kontak-kami.index', ['status' => 'belum-dibaca', 'page' => 2]);

    $this->actingAs($this->admin)
        ->from($asal)
        ->patch(route('admin.kontak-kami.status-baca', $pesan), ['status_baca' => '1'])
        ->assertRedirect($asal)
        ->assertSessionHas('success', 'Pesan ditandai sudah dibaca.');

    expect($pesan->refresh()->status_baca)->toBeTrue();

    $this->actingAs($this->admin)
        ->from($asal)
        ->patch(route('admin.kontak-kami.status-baca', $pesan), ['status_baca' => '0'])
        ->assertRedirect($asal)
        ->assertSessionHas('success', 'Pesan ditandai belum dibaca.');

    expect($pesan->refresh()->status_baca)->toBeFalse();
});

it('menandai pesan dibaca lewat JSON saat modal detail dibuka', function () {
    $pesan = KontakKami::factory()->belumDibaca()->create();
    KontakKami::factory()->belumDibaca()->count(2)->create();

    $this->actingAs($this->admin)
        ->patchJson(route('admin.kontak-kami.status-baca', $pesan), ['status_baca' => true])
        ->assertOk()
        ->assertExactJson(['status_baca' => true, 'belum_dibaca' => 2]);

    expect($pesan->refresh()->status_baca)->toBeTrue();
});

it('memvalidasi status baca', function (mixed $nilai) {
    $pesan = KontakKami::factory()->belumDibaca()->create();

    $this->actingAs($this->admin)
        ->patchJson(route('admin.kontak-kami.status-baca', $pesan), ['status_baca' => $nilai])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status_baca');

    expect($pesan->refresh()->status_baca)->toBeFalse();
})->with(['kosong' => [null], 'bukan boolean' => ['ya']]);

it('menampilkan badge jumlah pesan belum dibaca di sidebar', function () {
    KontakKami::factory()->belumDibaca()->count(3)->create();
    KontakKami::factory()->dibaca()->create();
    // Pesan terhapus tidak ikut dihitung.
    KontakKami::factory()->belumDibaca()->create()->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSeeInOrder(['Kontak Kami</span>', '<span class="badge badge-red" data-badge="kontak-kami-belum-dibaca" >3</span>'], false);
});

it('menyembunyikan badge sidebar bila semua pesan sudah dibaca', function () {
    KontakKami::factory()->dibaca()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSee('data-badge="kontak-kami-belum-dibaca"  hidden >0</span>', false);
});

it('menghapus pesan secara soft delete', function () {
    $pesan = KontakKami::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.kontak-kami.destroy', $pesan))
        ->assertRedirect(route('admin.kontak-kami.index'))
        ->assertSessionHas('success', 'Pesan berhasil dihapus.');

    $this->assertSoftDeleted($pesan);
});
