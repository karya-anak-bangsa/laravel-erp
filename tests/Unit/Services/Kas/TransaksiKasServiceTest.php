<?php

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use App\Services\Kas\TransaksiKasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->layanan = app(TransaksiKasService::class);
    $this->pengguna = Pengguna::factory()->create();
    $this->akun = AkunKas::factory()->create(['tanggal_saldo_awal' => '2026-01-01']);
    $this->kategoriKeluar = KategoriTransaksi::factory()->create(['nama_kategori' => 'Domain & Hosting']);
    $this->kategoriMasuk = KategoriTransaksi::factory()->pemasukan()->create(['nama_kategori' => 'Jasa Pembuatan Website']);
});

function dataTransaksi(KategoriTransaksi $kategori, AkunKas $akun, array $timpa = []): array
{
    return array_merge([
        'tanggal_transaksi' => '2026-09-15',
        'id_akun_kas' => $akun->id_akun_kas,
        'id_kategori_transaksi' => $kategori->id_kategori_transaksi,
        'jumlah' => '1250000',
        'nama_pihak' => 'Hostinger',
        'keterangan' => 'Perpanjangan domain',
    ], $timpa);
}

describe('penomoran', function () {
    it('memulai nomor dari 0001 dengan awalan jenis dan bulan tanggal transaksi', function () {
        expect($this->layanan->nomorBerikutnya(JenisTransaksi::Pemasukan, Carbon::parse('2026-09-30')))->toBe('KM-202609-0001')
            ->and($this->layanan->nomorBerikutnya(JenisTransaksi::Pengeluaran, Carbon::parse('2026-10-01')))->toBe('KK-202610-0001');
    });

    it('melanjutkan urutan per jenis per bulan secara terpisah', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-0007', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KM-202609-0002', 'id_kategori_transaksi' => $this->kategoriMasuk, 'tanggal_transaksi' => '2026-09-01']);

        expect($this->layanan->nomorBerikutnya(JenisTransaksi::Pengeluaran, Carbon::parse('2026-09-20')))->toBe('KK-202609-0008')
            ->and($this->layanan->nomorBerikutnya(JenisTransaksi::Pemasukan, Carbon::parse('2026-09-20')))->toBe('KM-202609-0003')
            ->and($this->layanan->nomorBerikutnya(JenisTransaksi::Pengeluaran, Carbon::parse('2026-10-20')))->toBe('KK-202610-0001');
    });

    it('tidak memakai ulang nomor transaksi yang sudah dihapus', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-0001', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01'])->delete();

        expect($this->layanan->nomorBerikutnya(JenisTransaksi::Pengeluaran, Carbon::parse('2026-09-20')))->toBe('KK-202609-0002');
    });

    it('tetap berurutan setelah melewati 9999', function () {
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-9999', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);
        TransaksiKas::factory()->create(['nomor_transaksi' => 'KK-202609-10000', 'id_kategori_transaksi' => $this->kategoriKeluar, 'tanggal_transaksi' => '2026-09-01']);

        expect($this->layanan->nomorBerikutnya(JenisTransaksi::Pengeluaran, Carbon::parse('2026-09-20')))->toBe('KK-202609-10001');
    });
});

describe('simpan', function () {
    it('mengambil jenis dari kategori dan mencatat pembuat serta pengubah', function () {
        $transaksi = $this->layanan->simpan(dataTransaksi($this->kategoriMasuk, $this->akun), null, $this->pengguna);

        expect($transaksi->jenis_transaksi)->toBe(JenisTransaksi::Pemasukan)
            ->and($transaksi->nomor_transaksi)->toBe('KM-202609-0001')
            ->and($transaksi->jumlah)->toBe('1250000.00')
            ->and($transaksi->created_by)->toBe($this->pengguna->id_pengguna)
            ->and($transaksi->updated_by)->toBe($this->pengguna->id_pengguna)
            ->and($transaksi->bukti_transaksi)->toBeNull();
    });

    it('menyimpan bukti ke disk privat', function () {
        $transaksi = $this->layanan->simpan(
            dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf'),
            $this->pengguna,
        );

        expect($transaksi->bukti_transaksi)->toStartWith('kas/bukti/');
        Storage::disk('local')->assertExists($transaksi->bukti_transaksi);
    });

    it('membuang bukti yang terlanjur diunggah bila penyimpanan gagal', function () {
        $data = dataTransaksi($this->kategoriKeluar, $this->akun, ['id_kategori_transaksi' => 999999]);

        expect(fn () => $this->layanan->simpan($data, UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf'), $this->pengguna))
            ->toThrow(ModelNotFoundException::class);

        expect(Storage::disk('local')->allFiles('kas/bukti'))->toBeEmpty()
            ->and(TransaksiKas::count())->toBe(0);
    });
});

describe('ubah', function () {
    beforeEach(function () {
        $this->transaksi = $this->layanan->simpan(dataTransaksi($this->kategoriKeluar, $this->akun), null, $this->pengguna);
        $this->pengubah = Pengguna::factory()->create();
    });

    it('memperbarui data dan pengubah tanpa mengganti nomor di bulan yang sama', function () {
        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun, [
            'tanggal_transaksi' => '2026-09-28',
            'jumlah' => '500000',
        ]), null, false, $this->pengubah);

        $this->transaksi->refresh();
        expect($this->transaksi->nomor_transaksi)->toBe('KK-202609-0001')
            ->and($this->transaksi->jumlah)->toBe('500000.00')
            ->and($this->transaksi->created_by)->toBe($this->pengguna->id_pengguna)
            ->and($this->transaksi->updated_by)->toBe($this->pengubah->id_pengguna);
    });

    it('memberi nomor baru bila tanggal pindah ke bulan lain', function () {
        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun, ['tanggal_transaksi' => '2026-10-02']), null, false, $this->pengubah);

        expect($this->transaksi->refresh()->nomor_transaksi)->toBe('KK-202610-0001');
    });

    it('menolak kategori dengan jenis berbeda', function () {
        expect(fn () => $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriMasuk, $this->akun), null, false, $this->pengubah))
            ->toThrow(ValidationException::class, 'Kategori harus berjenis pengeluaran seperti transaksi semula.');

        expect($this->transaksi->refresh()->id_kategori_transaksi)->toBe($this->kategoriKeluar->id_kategori_transaksi);
    });

    it('mengganti bukti dan membuang berkas lama setelah tersimpan', function () {
        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('lama.pdf', 10, 'application/pdf'), false, $this->pengubah);
        $pathLama = $this->transaksi->refresh()->bukti_transaksi;

        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'), false, $this->pengubah);
        $pathBaru = $this->transaksi->refresh()->bukti_transaksi;

        expect($pathBaru)->not->toBe($pathLama);
        Storage::disk('local')->assertMissing($pathLama);
        Storage::disk('local')->assertExists($pathBaru);
    });

    it('menghapus bukti bila diminta tanpa unggahan baru', function () {
        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('nota.pdf', 10, 'application/pdf'), false, $this->pengubah);
        $path = $this->transaksi->refresh()->bukti_transaksi;

        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun), null, true, $this->pengubah);

        expect($this->transaksi->refresh()->bukti_transaksi)->toBeNull();
        Storage::disk('local')->assertMissing($path);
    });

    it('mempertahankan bukti lama dan membuang unggahan baru bila perubahan gagal', function () {
        $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('lama.pdf', 10, 'application/pdf'), false, $this->pengubah);
        $pathLama = $this->transaksi->refresh()->bukti_transaksi;

        expect(fn () => $this->layanan->ubah($this->transaksi, dataTransaksi($this->kategoriMasuk, $this->akun),
            UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'), false, $this->pengubah))
            ->toThrow(ValidationException::class);

        expect($this->transaksi->refresh()->bukti_transaksi)->toBe($pathLama)
            ->and(Storage::disk('local')->allFiles('kas/bukti'))->toBe([$pathLama]);
    });
});

describe('hapus', function () {
    it('menghapus secara soft delete, mencatat pengubah, dan mempertahankan bukti', function () {
        $transaksi = $this->layanan->simpan(dataTransaksi($this->kategoriKeluar, $this->akun),
            UploadedFile::fake()->create('nota.pdf', 10, 'application/pdf'), $this->pengguna);
        $penghapus = Pengguna::factory()->create();

        $this->layanan->hapus($transaksi, $penghapus);

        $this->assertSoftDeleted($transaksi);
        expect(TransaksiKas::withTrashed()->find($transaksi->id_transaksi_kas)->updated_by)->toBe($penghapus->id_pengguna);
        Storage::disk('local')->assertExists($transaksi->bukti_transaksi);
    });
});
