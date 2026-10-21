<?php

use App\Enums\Sistem\AksiAktivitas;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Portofolio;
use App\Models\Pengguna;
use App\Models\Sistem\LogAktivitas;
use App\Services\Sistem\LogAktivitasService;

beforeEach(function () {
    $this->layanan = app(LogAktivitasService::class);
    $this->admin = Pengguna::factory()->create();
});

it('tidak mencatat perubahan data tanpa pengguna yang login', function () {
    $faq = Faq::factory()->create();

    $this->layanan->catatData(AksiAktivitas::Ubah, $faq, $faq->pertanyaan, ['Pertanyaan']);
    $faq->update(['pertanyaan' => 'Pertanyaan baru?']);
    $faq->delete();

    expect(LogAktivitas::count())->toBe(0);
});

it('mencatat perubahan data dengan alias jenis data, nama saat itu, dan alamat IP', function () {
    $faq = Faq::factory()->create(['pertanyaan' => 'Berapa lama pembuatan website?']);
    $this->actingAs($this->admin);

    $this->layanan->catatData(AksiAktivitas::Ubah, $faq, $faq->pertanyaan, ['Pertanyaan', 'Jawaban']);

    $log = LogAktivitas::query()->sole();
    expect($log->id_pengguna)->toBe($this->admin->id_pengguna)
        ->and($log->aksi)->toBe(AksiAktivitas::Ubah)
        // Alias morph map, bukan nama class.
        ->and($log->subjek_type)->toBe('faq')
        ->and($log->subjek_id)->toBe($faq->id_faq)
        ->and($log->jenis_data)->toBe('FAQ')
        ->and($log->label_subjek)->toBe('Berapa lama pembuatan website?')
        ->and($log->perubahan)->toBe(['Pertanyaan', 'Jawaban'])
        ->and($log->alamat_ip)->toBe('127.0.0.1')
        ->and($log->created_at)->not->toBeNull();
});

it('mencatat masuk tanpa data dan memotong nama data sampai 255 karakter', function () {
    $this->layanan->catat(AksiAktivitas::Masuk, $this->admin);
    $faq = Faq::factory()->create();
    $this->layanan->catat(AksiAktivitas::Hapus, $this->admin, $faq, str_repeat('a', 300));

    [$masuk, $hapus] = LogAktivitas::query()->oldest('id_log_aktivitas')->get();
    expect($masuk->subjek_type)->toBeNull()
        ->and($masuk->jenis_data)->toBeNull()
        ->and($masuk->label_subjek)->toBeNull()
        ->and($masuk->perubahan)->toBeNull()
        ->and(mb_strlen($hapus->label_subjek))->toBe(255);
});

it('menghapus permanen log yang melewati masa simpan 7 hari setiap kali log baru ditulis', function () {
    $lama = LogAktivitas::factory()->for($this->admin, 'pengguna')->create(['created_at' => now()->subDays(7)->subMinute()]);
    $masihDisimpan = LogAktivitas::factory()->for($this->admin, 'pengguna')->create(['created_at' => now()->subDays(7)->addMinute()]);

    $baru = $this->layanan->catat(AksiAktivitas::Masuk, $this->admin);

    expect(LogAktivitas::query()->pluck('id_log_aktivitas')->sort()->values()->all())
        ->toBe([$masihDisimpan->id_log_aktivitas, $baru->id_log_aktivitas])
        ->and(LogAktivitas::find($lama->id_log_aktivitas))->toBeNull();
});

it('mengosongkan semua log dan menyisakan satu baris pengosongan oleh penggunanya', function () {
    LogAktivitas::factory()->count(3)->create();

    $this->layanan->kosongkan($this->admin);

    $log = LogAktivitas::query()->sole();
    expect($log->aksi)->toBe(AksiAktivitas::Kosongkan)
        ->and($log->aksi->label())->toBe('Kosongkan log')
        ->and($log->id_pengguna)->toBe($this->admin->id_pengguna)
        ->and($log->subjek_type)->toBeNull()
        ->and($log->alamat_ip)->toBe('127.0.0.1');
});

it('mencatat isian yang ditandai walau tidak ada kolom tabel yang berubah', function () {
    // Dibuat dan disimpan dalam detik yang sama, jadi updated_at pun tidak berubah dan event updated tidak terpicu.
    $portofolio = Portofolio::factory()->create(['judul' => 'Website Sekolah']);
    $this->actingAs($this->admin);

    $portofolio->tandaiBerubah('galeri');
    $portofolio->save();
    // Tanda hanya berlaku untuk satu penyimpanan.
    $portofolio->save();

    expect(LogAktivitas::query()->get(['aksi', 'label_subjek', 'perubahan'])->toArray())->toBe([
        ['aksi' => 'ubah', 'label_subjek' => 'Website Sekolah', 'perubahan' => ['Gambar Tambahan']],
    ]);
});

it('mengabaikan kolom yang tidak berlabel isian, seperti hero lain yang dinonaktifkan lewat query massal', function () {
    $hero = Hero::factory()->aktif()->create(['judul' => 'Hero Lama']);
    $this->actingAs($this->admin);
    $this->travel(1)->minutes();

    // updated_at tidak berlabel isian; query massal tidak memicu event model.
    $hero->touch();
    Hero::query()->update(['status_aktif' => false]);

    expect(LogAktivitas::count())->toBe(0);
});
