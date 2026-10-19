<?php

use App\Services\Shared\FileUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->layanan = new FileUploadService;
});

it('menyimpan berkas dengan nama UUID dan ekstensi sesuai isi berkas', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->create('dokumen asli.pdf', 10, 'application/pdf'), 'uji/dokumen', 'local');

    expect($path)->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($path);
});

/** Ukuran gambar yang tersimpan di disk local, [lebar, tinggi, mime]. */
function ukuranTersimpan(string $path): array
{
    $ukuran = getimagesizefromstring((string) Storage::disk('local')->get($path));

    return [$ukuran[0], $ukuran[1], $ukuran['mime']];
}

/**
 * JPEG hitam berkotak merah di pojok kiri atas, bertag EXIF Orientation (IFD0 minimal, TIFF big-endian) tepat
 * setelah penanda SOI.
 */
function jpegBerorientasi(int $lebar, int $tinggi, int $orientasi): string
{
    $gambar = imagecreatetruecolor($lebar, $tinggi);
    imagefilledrectangle($gambar, 0, 0, intdiv($tinggi, 4), intdiv($tinggi, 4), (int) imagecolorallocate($gambar, 255, 0, 0));
    ob_start();
    imagejpeg($gambar);
    $jpeg = (string) ob_get_clean();
    $exif = "Exif\0\0MM\0\x2A".pack('N', 8).pack('n', 1).pack('nnN', 0x0112, 3, 1).pack('n', $orientasi)."\0\0".pack('N', 0);

    return "\xFF\xD8\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
}

/** PNG polos dengan chunk iCCP (profil warna) bernama $namaProfil, tepat setelah chunk IHDR. */
function pngBerprofil(int $lebar, int $tinggi, string $namaProfil): string
{
    ob_start();
    imagepng(imagecreatetruecolor($lebar, $tinggi));
    $png = (string) ob_get_clean();
    $data = "ICC Profile\0\0".gzcompress("lcms\0desc {$namaProfil}");

    return substr($png, 0, 33).pack('N', strlen($data)).'iCCP'.$data.pack('N', crc32('iCCP'.$data)).substr($png, 33);
}

it('memperkecil gambar besar menjadi WebP bersisi terpanjang 1600 px bila dioptimalkan', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->image('besar.png', 3200, 1800), 'uji/gambar', 'local', optimalkanGambar: true);

    expect($path)->toMatch('#^uji/gambar/[0-9a-f-]{36}\.webp$#')
        ->and(ukuranTersimpan($path))->toBe([FileUploadService::SISI_MAKS_GAMBAR, 900, 'image/webp']);
});

it('mempertahankan transparansi saat memperkecil gambar', function () {
    $gambar = imagecreatetruecolor(3200, 1800);
    imagealphablending($gambar, false);
    imagesavealpha($gambar, true);
    imagefill($gambar, 0, 0, (int) imagecolorallocatealpha($gambar, 0, 0, 0, 127));
    ob_start();
    imagepng($gambar);
    $berkas = UploadedFile::fake()->createWithContent('transparan.png', (string) ob_get_clean());

    $path = $this->layanan->simpan($berkas, 'uji/gambar', 'local', optimalkanGambar: true);
    $hasil = imagecreatefromstring((string) Storage::disk('local')->get($path));

    expect($path)->toEndWith('.webp')
        ->and(imagecolorsforindex($hasil, imagecolorat($hasil, 800, 450))['alpha'])->toBe(127);
});

it('tidak memperbesar gambar yang lebih kecil dari batas', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->image('kecil.jpg', 800, 600), 'uji/gambar', 'local', optimalkanGambar: true);

    expect(array_slice(ukuranTersimpan($path), 0, 2))->toBe([800, 600]);
});

it('menyimpan WebP yang tidak perlu diperkecil apa adanya', function () {
    $berkas = UploadedFile::fake()->image('kecil.webp', 800, 600);

    $path = $this->layanan->simpan($berkas, 'uji/gambar', 'local', optimalkanGambar: true);

    expect(Storage::disk('local')->get($path))->toBe($berkas->get());
});

it('mempertahankan format asli bila gambar tidak dioptimalkan', function () {
    $path = $this->layanan->simpan(UploadedFile::fake()->image('logo.png', 3200, 1800), 'uji/gambar', 'local');

    expect($path)->toEndWith('.png')
        ->and(ukuranTersimpan($path))->toBe([3200, 1800, 'image/png']);
});

it('memutar foto JPEG sesuai orientasi EXIF', function (int $orientasi, array $ukuran, array $pojokMerah) {
    $berkas = UploadedFile::fake()->createWithContent('foto.jpg', jpegBerorientasi(3200, 1800, $orientasi));

    $path = $this->layanan->simpan($berkas, 'uji/gambar', 'local', optimalkanGambar: true);
    $gambar = imagecreatefromstring((string) Storage::disk('local')->get($path));
    $warna = imagecolorsforindex($gambar, imagecolorat($gambar, ...$pojokMerah));

    // Kotak merah di pojok kiri atas berkas asli harus berada di pojok yang tampil di browser.
    expect(ukuranTersimpan($path))->toBe([...$ukuran, 'image/webp'])
        ->and($warna['red'])->toBeGreaterThan(200)
        ->and($warna['green'])->toBeLessThan(60);
})->with([
    'tegak' => [1, [1600, 900], [20, 20]],
    'diputar 90° searah jarum jam' => [6, [900, 1600], [880, 20]],
    'diputar 90° berlawanan jarum jam' => [8, [900, 1600], [20, 1580]],
]);

it('hanya mengonversi gambar berprofil warna sRGB', function (string $profil, string $ekstensi) {
    $berkas = UploadedFile::fake()->createWithContent('ilustrasi.png', pngBerprofil(3200, 1800, $profil));

    $path = $this->layanan->simpan($berkas, 'uji/gambar', 'local', optimalkanGambar: true);

    // Profil selain sRGB terbuang oleh GD sehingga warnanya bergeser; berkas asli disimpan utuh.
    expect($path)->toEndWith($ekstensi);
    if ($ekstensi === '.png') {
        expect(Storage::disk('local')->get($path))->toBe($berkas->get());
    }
})->with([
    'sRGB' => ['sRGB IEC61966-2.1', '.webp'],
    'Display P3' => ['Display P3', '.png'],
]);

it('menyimpan berkas asli bila gambar tidak terbaca', function () {
    $berkas = UploadedFile::fake()->createWithContent('rusak.png', 'bukan gambar');

    $path = $this->layanan->simpan($berkas, 'uji/gambar', 'local', optimalkanGambar: true);

    expect($path)->toEndWith('.png')
        ->and(Storage::disk('local')->get($path))->toBe('bukan gambar');
});

it('menghapus berkas yang ada', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');

    $this->layanan->hapus('uji/dokumen/lama.pdf', 'local');

    Storage::disk('local')->assertMissing('uji/dokumen/lama.pdf');
});

it('mengabaikan path kosong saat menghapus', function () {
    Storage::disk('local')->put('uji/dokumen/tetap.pdf', 'isi');

    $this->layanan->hapus(null, 'local');
    $this->layanan->hapus('', 'local');

    Storage::disk('local')->assertExists('uji/dokumen/tetap.pdf');
});

it('menjalankan penyimpanan dengan path berkas baru lalu menghapus berkas lama', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');
    $model = new class extends Model {};
    $model->setRawAttributes(['berkas' => 'uji/dokumen/lama.pdf', 'lampiran' => 'uji/dokumen/tetap.pdf']);
    $diterima = null;

    $this->layanan->simpanDenganBerkas($model, [
        'judul' => 'Uji',
        'berkas' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
        'lampiran' => null,
    ], ['berkas', 'lampiran'], 'uji/dokumen', 'local', function (array $data) use ($model, &$diterima) {
        $diterima = $data;

        return $model;
    });

    // Kolom tanpa berkas baru (lampiran) dibuang agar path lamanya tidak tertimpa null.
    expect($diterima)->toHaveKeys(['judul', 'berkas'])->not->toHaveKey('lampiran')
        ->and($diterima['berkas'])->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($diterima['berkas']);
    Storage::disk('local')->assertMissing('uji/dokumen/lama.pdf');
});

it('meneruskan daftar path untuk kolom berisi banyak berkas', function () {
    $diterima = null;

    $this->layanan->simpanDenganBerkas(null, [
        'lampiran' => [
            UploadedFile::fake()->create('satu.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('dua.pdf', 10, 'application/pdf'),
        ],
        'kosong' => [],
    ], ['lampiran', 'kosong'], 'uji/dokumen', 'local', function (array $data) use (&$diterima) {
        $diterima = $data;

        return new class extends Model {};
    });

    // Daftar tanpa berkas dibuang seperti kolom tanpa berkas baru.
    expect($diterima)->toHaveKey('lampiran')->not->toHaveKey('kosong')
        ->and($diterima['lampiran'])->toHaveCount(2)
        ->each->toMatch('#^uji/dokumen/[0-9a-f-]{36}\.pdf$#');
    Storage::disk('local')->assertExists($diterima['lampiran']);
});

it('menghapus semua berkas dari daftar bila penyimpanan gagal', function () {
    expect(fn () => $this->layanan->simpanDenganBerkas(null, [
        'berkas' => UploadedFile::fake()->create('utama.pdf', 10, 'application/pdf'),
        'lampiran' => [
            UploadedFile::fake()->create('satu.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('dua.pdf', 10, 'application/pdf'),
        ],
    ], ['berkas', 'lampiran'], 'uji/dokumen', 'local', fn () => throw new RuntimeException('gagal')))
        ->toThrow(RuntimeException::class, 'gagal');

    expect(Storage::disk('local')->allFiles('uji/dokumen'))->toBe([]);
});

it('menghapus berkas baru dan mempertahankan berkas lama bila penyimpanan gagal', function () {
    Storage::disk('local')->put('uji/dokumen/lama.pdf', 'isi');
    $model = new class extends Model {};
    $model->setRawAttributes(['berkas' => 'uji/dokumen/lama.pdf']);

    expect(fn () => $this->layanan->simpanDenganBerkas($model, [
        'berkas' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
    ], ['berkas'], 'uji/dokumen', 'local', fn () => throw new RuntimeException('gagal')))
        ->toThrow(RuntimeException::class, 'gagal');

    expect(Storage::disk('local')->allFiles('uji/dokumen'))->toBe(['uji/dokumen/lama.pdf']);
});
