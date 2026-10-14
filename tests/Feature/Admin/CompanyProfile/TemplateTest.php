<?php

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\NadaDasar;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\Template;
use App\Models\Pengguna;
use App\Services\CompanyProfile\TemaService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Pengguna::factory()->create();
});

function dataTemplateValid(array $timpa = []): array
{
    return [
        'nama' => 'Biru Laut',
        'jenis' => 'full_color',
        'pengaturan' => 'kustom',
        'warna_utama' => '#0C4A6E',
        'warna_aksen' => '#0EA5E9',
        'nada_dasar' => 'netral',
        'font' => 'poppins',
        'sudut' => 'bulat',
        'skala' => 'standar',
        'lebar_konten' => 'standar',
        'ketebalan_judul' => 'bold',
        'bayangan' => 'tegas',
        'status_aktif' => '0',
        ...$timpa,
    ];
}

it('mengarahkan tamu ke halaman login', function () {
    $template = Template::factory()->create();

    $this->get(route('admin.template.index'))->assertRedirect(route('login'));
    $this->get(route('admin.template.create'))->assertRedirect(route('login'));
    $this->post(route('admin.template.store'), dataTemplateValid())->assertRedirect(route('login'));
    $this->get(route('admin.template.edit', $template))->assertRedirect(route('login'));
    $this->put(route('admin.template.update', $template), dataTemplateValid())->assertRedirect(route('login'));
    $this->delete(route('admin.template.destroy', $template))->assertRedirect(route('login'));
    $this->get(route('admin.template.pratinjau', dataTemplateValid()))->assertRedirect(route('login'));
});

it('menampilkan daftar template: aktif di atas, lalu per jenis dan abjad', function () {
    Template::factory()->monochrome()->create(['nama' => 'Abu Hangat']);
    Template::factory()->create(['nama' => 'Zamrud']);
    Template::factory()->create(['nama' => 'Biru Laut']);
    Template::factory()->monochrome()->aktif()->create(['nama' => 'TKAB Monochrome']);
    Template::factory()->aktif()->create(['nama' => 'TKAB Full Color']);

    $this->actingAs($this->admin)
        ->get(route('admin.template.index'))
        ->assertOk()
        ->assertSee('<h1 class="page-title">Pengaturan Sistem</h1>', false)
        ->assertSee('class="nav-link active" href="'.route('admin.template.index').'"', false)
        ->assertSeeInOrder(['TKAB Full Color', 'TKAB Monochrome', 'Biru Laut', 'Zamrud', 'Abu Hangat']);
});

it('menampilkan rincian, tautan pratinjau, dan tombol hapus yang terkunci untuk template aktif', function () {
    $aktif = Template::factory()->aktif()->create(['nama' => 'Aktif', 'warna_utama' => '#15253f', 'warna_aksen' => '#cb1839', 'bayangan' => 'tegas']);
    $nonaktif = Template::factory()->monochrome()->create(['nama' => 'Cadangan', 'nada_dasar' => NadaDasar::Cokelat, 'bayangan' => 'tanpa']);

    $respons = $this->actingAs($this->admin)->get(route('admin.template.index'));

    $respons->assertSeeInOrder([
        'judul semibold · bayangan tegas',
        'data-detail-title="Detail Template"',
        '<th scope="row">Pengaturan</th>',
        '<td>Custom</td>',
        '#15253f',
        '#cb1839',
        '<th scope="row">Bayangan</th>',
        '<td>Tegas</td>',
        'href="'.e(route('admin.template.pratinjau', $aktif->nilaiTampilan())).'"',
        '</template>',
        'href="'.route('admin.template.edit', $aktif).'"',
        'title="Template aktif tidak bisa dihapus"',
    ], false)
        ->assertDontSee('action="'.route('admin.template.destroy', $aktif).'"', false)
        ->assertSee('action="'.route('admin.template.destroy', $nonaktif).'"', false)
        ->assertSee('Cokelat (abu kecokelatan)')
        ->assertSee('judul semibold · tanpa bayangan');

    expect($aktif->nilaiTampilan())->toHaveKey('bayangan', 'tegas');
});

it('menampilkan pengaturan Default di rincian template yang memakai nilai bawaan', function () {
    Template::factory()->monochrome()->create(['nama' => 'Bawaan', ...app(TemaService::class)->nilaiBawaan(JenisTemplate::Monochrome)]);

    $this->actingAs($this->admin)
        ->get(route('admin.template.index'))
        ->assertSeeInOrder(['<th scope="row">Pengaturan</th>', '<td>Default</td>', 'Netral (hitam-putih)'], false);
});

it('mencari template berdasarkan nama serta memfilter jenis dan status', function () {
    Template::factory()->aktif()->create(['nama' => 'Merah Merek']);
    Template::factory()->monochrome()->create(['nama' => 'Abu Netral']);

    $this->actingAs($this->admin)->get(route('admin.template.index', ['q' => 'merah']))
        ->assertSee('Merah Merek')->assertDontSee('Abu Netral');
    $this->actingAs($this->admin)->get(route('admin.template.index', ['jenis' => 'monochrome']))
        ->assertSee('Abu Netral')->assertDontSee('Merah Merek');
    $this->actingAs($this->admin)->get(route('admin.template.index', ['status' => 'aktif']))
        ->assertSee('Merah Merek')->assertDontSee('Abu Netral');
});

it('membedakan empty state data kosong dan hasil filter kosong', function () {
    $this->actingAs($this->admin)->get(route('admin.template.index'))
        ->assertSee('Belum ada template')
        ->assertSee('Tambah Template Pertama');

    Template::factory()->create();

    $this->actingAs($this->admin)->get(route('admin.template.index', ['q' => 'tidak-ada']))
        ->assertSee('Template tidak ditemukan');
});

it('mengisi form tambah dengan salinan template aktif dalam keadaan nonaktif', function () {
    Template::factory()->aktif()->create(['warna_aksen' => '#0ea5e9', 'font' => 'manrope', 'bayangan' => 'tegas']);

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.template.create'))
        ->assertOk()
        ->assertSee('<input type="color" value="#0ea5e9" data-pilih-warna="warna_aksen"', false)
        ->assertSee('<option value="manrope" selected>', false)
        ->assertSee('<option value="tegas" selected>', false)
        // Salinan berbeda dari nilai bawaan, jadi mulai sebagai Custom dengan isian terbuka.
        ->assertSee('<option value="kustom" selected>Custom</option>', false)
        ->assertDontSee('name="status_aktif" value="1" checked', false)
        ->assertSee('data-khusus-jenis="full_color" >', false)
        ->assertSee('data-khusus-jenis="monochrome" hidden>', false)
        // Kartu kanan "Status dan Pratinjau": status lalu tombol pratinjau selebar kartu; baris bawah Simpan & Batal.
        ->assertSeeInOrder([
            '<div class="card-title">Tampilan Template</div>',
            'name="nama"',
            '</fieldset>',
            '<div class="card-title">Status dan Pratinjau</div>',
            'name="status_aktif"',
            '<button type="button" class="btn btn-info btn-blok" data-pratinjau-template="'.route('admin.template.pratinjau').'"',
            'Buka pratinjau',
            '<div class="form-actions">',
        ], false);

    expect($respons->getContent())->toMatch('/<fieldset class="isian-tampilan" data-isian-tampilan data-bawaan="[^"]+"\s*>/')
        ->and(substr_count($respons->getContent(), 'data-pratinjau-template='))->toBe(1);
});

it('membuka form dengan pengaturan Default dan isian terkunci bila nilai template sama dengan bawaan', function () {
    $temaService = app(TemaService::class);
    $template = Template::factory()->create($temaService->nilaiBawaan(JenisTemplate::FullColor));
    $bawaan = ['full_color' => $temaService->nilaiBawaan(JenisTemplate::FullColor), 'monochrome' => $temaService->nilaiBawaan(JenisTemplate::Monochrome)];

    $respons = $this->actingAs($this->admin)
        ->get(route('admin.template.edit', $template))
        ->assertOk()
        ->assertSee('<option value="bawaan" selected>Default</option>', false)
        // Nilai bawaan kedua jenis untuk template.js (Default dipilih atau jenis diganti).
        ->assertSee('data-bawaan="'.e(json_encode($bawaan)).'"', false);

    expect($respons->getContent())->toMatch('/<fieldset class="isian-tampilan"[^>]* disabled>/');
});

it('menyimpan nilai bawaan jenisnya bila pengaturan Default walau isian tampilan tidak terkirim', function (string $jenis) {
    // Isian dalam fieldset disabled tidak ikut terkirim browser.
    $this->actingAs($this->admin)
        ->post(route('admin.template.store'), ['nama' => 'Ikut Bawaan', 'jenis' => $jenis, 'pengaturan' => 'bawaan', 'status_aktif' => '0'])
        ->assertRedirect(route('admin.template.index'))
        ->assertSessionHasNoErrors();

    $template = Template::sole();
    $temaService = app(TemaService::class);
    expect(array_intersect_key($template->nilaiTampilan(), $temaService->nilaiBawaan($template->jenis)))->toEqual($temaService->nilaiBawaan($template->jenis))
        ->and($temaService->pengaturan($template)->value)->toBe('bawaan');
})->with(['full_color', 'monochrome']);

it('mengembalikan template ke nilai bawaan lewat pengaturan Default dan mengabaikan isian tampilan yang dikirim', function () {
    $template = Template::factory()->monochrome()->create(['nama' => 'Coba-coba', 'nada_dasar' => NadaDasar::Ungu, 'bayangan' => 'tegas', 'skala' => 'lega']);

    $this->actingAs($this->admin)
        ->put(route('admin.template.update', $template), dataTemplateValid(['nama' => 'Coba-coba', 'jenis' => 'monochrome', 'pengaturan' => 'bawaan', 'nada_dasar' => 'ungu']))
        ->assertRedirect(route('admin.template.index'));

    $template->refresh();
    expect($template->nada_dasar)->toBe(NadaDasar::Netral)
        ->and($template->bayangan)->toBe(BayanganTemplate::Tanpa)
        ->and($template->skala->value)->toBe('standar')
        ->and($template->font->value)->toBe('geist')
        ->and($template->warna_utama)->toBeNull();
});

it('menyimpan template Full Color dengan hex huruf kecil dan tanpa nada dasar', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.template.store'), dataTemplateValid())
        ->assertRedirect(route('admin.template.index'))
        ->assertSessionHas('success');

    $template = Template::sole();
    expect($template->nama)->toBe('Biru Laut')
        ->and($template->warna_utama)->toBe('#0c4a6e')
        ->and($template->warna_aksen)->toBe('#0ea5e9')
        ->and($template->nada_dasar)->toBeNull()
        ->and($template->bayangan->value)->toBe('tegas')
        ->and($template->status_aktif)->toBeFalse();
});

it('menyimpan template Monochrome tanpa warna', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.template.store'), dataTemplateValid(['jenis' => 'monochrome', 'nada_dasar' => 'biru']))
        ->assertRedirect(route('admin.template.index'));

    $template = Template::sole();
    expect($template->jenis)->toBe(JenisTemplate::Monochrome)
        ->and($template->nada_dasar)->toBe(NadaDasar::Biru)
        ->and($template->warna_utama)->toBeNull()
        ->and($template->warna_aksen)->toBeNull();
});

it('menonaktifkan template sejenis saat template baru diaktifkan', function () {
    $lama = Template::factory()->aktif()->create();
    $mono = Template::factory()->monochrome()->aktif()->create();

    $this->actingAs($this->admin)->post(route('admin.template.store'), dataTemplateValid(['status_aktif' => '1']));

    expect(Template::where('nama', 'Biru Laut')->sole()->status_aktif)->toBeTrue()
        ->and($lama->refresh()->status_aktif)->toBeFalse()
        ->and($mono->refresh()->status_aktif)->toBeTrue();
});

it('memvalidasi isian sesuai jenis template', function (array $timpa, array $galat) {
    $this->actingAs($this->admin)
        ->from(route('admin.template.create'))
        ->post(route('admin.template.store'), dataTemplateValid($timpa))
        ->assertRedirect(route('admin.template.create'))
        ->assertSessionHasErrors($galat);

    expect(Template::count())->toBe(0);
})->with([
    'warna wajib untuk Full Color' => [['warna_utama' => '', 'warna_aksen' => null], ['warna_utama', 'warna_aksen']],
    'hex tidak valid' => [['warna_utama' => 'merah', 'warna_aksen' => '#abc'], ['warna_utama', 'warna_aksen']],
    'nada dasar wajib untuk Monochrome' => [['jenis' => 'monochrome', 'nada_dasar' => ''], ['nada_dasar']],
    'nada dasar lama yang sudah dibuang' => [['jenis' => 'monochrome', 'nada_dasar' => 'zinc'], ['nada_dasar']],
    'pengaturan wajib dipilih' => [['pengaturan' => ''], ['pengaturan']],
    'pengaturan di luar enum' => [['pengaturan' => 'acak'], ['pengaturan']],
    'pilihan di luar enum' => [['jenis' => 'gelap', 'font' => 'comic_sans', 'sudut' => 'lancip', 'skala' => 'raksasa', 'lebar_konten' => 'penuh', 'ketebalan_judul' => 'tipis', 'bayangan' => 'pekat'], ['jenis', 'font', 'sudut', 'skala', 'lebar_konten', 'ketebalan_judul', 'bayangan']],
    'bayangan wajib dipilih' => [['bayangan' => ''], ['bayangan']],
    'nama kosong' => [['nama' => ''], ['nama']],
]);

it('menolak nama yang sudah dipakai, kecuali milik template yang terhapus', function () {
    Template::factory()->create(['nama' => 'Biru Laut']);

    $this->actingAs($this->admin)->post(route('admin.template.store'), dataTemplateValid())
        ->assertSessionHasErrors(['nama' => 'Nama template sudah dipakai template lain.']);

    Template::where('nama', 'Biru Laut')->sole()->delete();

    $this->actingAs($this->admin)->post(route('admin.template.store'), dataTemplateValid())
        ->assertSessionHasNoErrors();
});

it('mengunci jenis dan status template aktif di form ubah', function () {
    $template = Template::factory()->aktif()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.template.edit', $template))
        ->assertOk()
        ->assertSee('<input type="hidden" name="jenis" value="full_color">', false)
        ->assertSee('disabled="disabled"', false)
        ->assertSee('<input type="hidden" name="status_aktif" value="1">', false)
        ->assertDontSee('class="switch"', false)
        ->assertSee('langsung tampil di website');
});

it('tidak menonaktifkan atau mengganti jenis template aktif walau request dibuat sendiri', function () {
    $template = Template::factory()->aktif()->create(['nama' => 'Aktif']);

    $this->actingAs($this->admin)
        ->put(route('admin.template.update', $template), dataTemplateValid(['nama' => 'Aktif Baru', 'jenis' => 'monochrome', 'status_aktif' => '0']))
        ->assertRedirect(route('admin.template.index'));

    $template->refresh();
    expect($template->nama)->toBe('Aktif Baru')
        ->and($template->jenis)->toBe(JenisTemplate::FullColor)
        ->and($template->warna_aksen)->toBe('#0ea5e9')
        ->and($template->status_aktif)->toBeTrue();
});

it('mengubah jenis template nonaktif dan mengosongkan kolom yang tidak relevan', function () {
    $template = Template::factory()->create(['nama' => 'Fleksibel']);

    $this->actingAs($this->admin)
        ->put(route('admin.template.update', $template), dataTemplateValid(['nama' => 'Fleksibel', 'jenis' => 'monochrome', 'nada_dasar' => 'hijau']))
        ->assertRedirect(route('admin.template.index'));

    $template->refresh();
    expect($template->jenis)->toBe(JenisTemplate::Monochrome)
        ->and($template->nada_dasar)->toBe(NadaDasar::Hijau)
        ->and($template->warna_utama)->toBeNull();
});

it('menghapus template nonaktif dan menolak menghapus template aktif', function () {
    $aktif = Template::factory()->aktif()->create(['nama' => 'Sedang Dipakai']);
    $nonaktif = Template::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.template.destroy', $nonaktif))
        ->assertRedirect(route('admin.template.index'))
        ->assertSessionHas('success');
    $this->assertSoftDeleted($nonaktif);

    $this->actingAs($this->admin)->delete(route('admin.template.destroy', $aktif))
        ->assertRedirect(route('admin.template.index'))
        ->assertSessionHas('error', 'Template “Sedang Dipakai” sedang aktif. Aktifkan template Full Color lain lebih dulu.');
    $this->assertNotSoftDeleted($aktif);
});

describe('pratinjau dan frontend', function () {
    beforeEach(function () {
        Identitas::factory()->create();
    });

    it('menampilkan beranda dengan nilai isian tanpa menyimpan apa pun', function () {
        $this->actingAs($this->admin)
            ->withUnencryptedCookie('tema', 'full_color')
            ->get(route('admin.template.pratinjau', dataTemplateValid(['jenis' => 'monochrome', 'nada_dasar' => 'cokelat', 'nama' => 'Abu <Hangat>'])))
            ->assertOk()
            ->assertSee('<html lang="id" data-tema="monochrome">', false)
            ->assertSee('--nada-50: oklch(98.5% 0.003 55);', false)
            ->assertSee('--font-tema: "Poppins"; --ketebalan-judul: 700;', false)
            // Bayangan Tegas Monochrome: formulir shadow-md.
            ->assertSee('--bayangan-formulir: 0 4px 6px -1px #0000001a, 0 2px 4px -2px #0000001a;', false)
            ->assertSee('Pratinjau template “Abu &lt;Hangat&gt;”', false);

        expect(Template::count())->toBe(0);
    });

    it('mempratinjau pengaturan Default dengan nilai bawaan walau isian tampilan tidak dikirim', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.template.pratinjau', ['nama' => 'Baru', 'jenis' => 'monochrome', 'pengaturan' => 'bawaan']))
            ->assertOk()
            ->assertSee('<html lang="id" data-tema="monochrome">', false)
            ->assertSee('html[data-tema=monochrome] { --nada-50: oklch(98.5% 0 0);', false)
            ->assertSee('--garis-kartu: var(--nada-300);', false);
    });

    it('menolak pratinjau dengan nilai tidak valid', function () {
        $this->actingAs($this->admin)
            ->from(route('admin.template.create'))
            ->get(route('admin.template.pratinjau', dataTemplateValid(['warna_aksen' => 'url(x)'])))
            ->assertRedirect(route('admin.template.create'))
            ->assertSessionHasErrors('warna_aksen');
    });

    it('memakai template aktif di beranda tanpa pita pratinjau', function () {
        Template::factory()->aktif()->create(['warna_aksen' => '#0ea5e9', 'ketebalan_judul' => 'extrabold', 'bayangan' => 'tegas']);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('--warna-aksen: #0ea5e9;', false)
            ->assertSee('--ketebalan-judul: 800;', false)
            // Kartu Full Color Tegas dari template aktif; Monochrome (Tanpa dari config) datar.
            ->assertSee('--bayangan-kartu: '.BayanganTemplate::Tegas->token(JenisTemplate::FullColor)['--bayangan-kartu'].';', false)
            ->assertSee('--bayangan-kartu: 0 0 #0000;', false)
            ->assertDontSee('pita-pratinjau', false);
    });
});
