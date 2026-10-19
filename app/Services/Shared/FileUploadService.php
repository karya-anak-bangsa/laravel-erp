<?php

namespace App\Services\Shared;

use Closure;
use GdImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FileUploadService
{
    /** Sisi terpanjang gambar konten: cukup untuk bingkai terlebar (hero, detail portofolio) di layar 2×. */
    public const SISI_MAKS_GAMBAR = 1600;

    public const KUALITAS_WEBP = 82;

    // GD memakai ±4 byte per piksel: 16 MP ≈ 64 MB, masih aman untuk memory_limit 128M.
    private const PIKSEL_MAKS_GAMBAR = 16_000_000;

    /**
     * Simpan berkas dengan nama UUID; mengembalikan path relatif terhadap disk. Dengan $optimalkanGambar, gambar
     * diperkecil dan disimpan sebagai WebP bila hasilnya lebih baik (lihat webp()).
     */
    public function simpan(UploadedFile $berkas, string $folder, string $disk, bool $optimalkanGambar = false): string
    {
        $webp = $optimalkanGambar ? $this->webp($berkas) : null;

        if ($webp !== null) {
            $path = $folder.'/'.Str::uuid().'.webp';

            if (! Storage::disk($disk)->put($path, $webp)) {
                throw new RuntimeException("Berkas gagal disimpan ke disk {$disk}.");
            }

            return $path;
        }

        // Ekstensi ditebak dari isi berkas (MIME), bukan nama asli yang bisa dipalsukan.
        $path = $berkas->storeAs($folder, Str::uuid().'.'.$berkas->extension(), $disk);

        if ($path === false) {
            throw new RuntimeException("Berkas gagal disimpan ke disk {$disk}.");
        }

        return $path;
    }

    public function hapus(?string $path, string $disk): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Menyimpan berkas unggahan pada $kolomBerkas, lalu menjalankan $simpan dengan
     * data yang kolom berkasnya sudah berisi path. Kolom tanpa berkas baru dibuang
     * dari data sehingga path lama tetap dipakai. Kolom berisi daftar berkas
     * (mis. gambar tambahan untuk baris anak) diteruskan sebagai daftar path.
     *
     * @template TModel of Model
     *
     * @param  TModel|null  $model  model yang diubah (null saat tambah); berkas lamanya dihapus setelah sukses
     * @param  array<string, mixed>  $data  hasil validasi
     * @param  list<string>  $kolomBerkas
     * @param  Closure(array<string, mixed>): TModel  $simpan
     * @param  bool  $optimalkanGambar  gambar konten publik diperkecil & disimpan WebP (lihat simpan())
     * @return TModel
     */
    public function simpanDenganBerkas(?Model $model, array $data, array $kolomBerkas, string $folder, string $disk, Closure $simpan, bool $optimalkanGambar = false): Model
    {
        $berkasBaru = [];
        $berkasLama = [];
        $terunggah = [];

        try {
            foreach ($kolomBerkas as $kolom) {
                $berkas = $data[$kolom] ?? null;
                unset($data[$kolom]);

                if ($berkas instanceof UploadedFile) {
                    $berkasBaru[$kolom] = $terunggah[] = $this->simpan($berkas, $folder, $disk, $optimalkanGambar);
                    $lama = $model?->getAttribute($kolom);

                    if (is_string($lama)) {
                        $berkasLama[] = $lama;
                    }
                } elseif (is_array($berkas)) {
                    $daftarPath = [];

                    foreach ($berkas as $satuBerkas) {
                        if ($satuBerkas instanceof UploadedFile) {
                            $daftarPath[] = $terunggah[] = $this->simpan($satuBerkas, $folder, $disk, $optimalkanGambar);
                        }
                    }

                    if ($daftarPath !== []) {
                        $berkasBaru[$kolom] = $daftarPath;
                    }
                }
            }

            $hasil = $simpan([...$data, ...$berkasBaru]);
        } catch (Throwable $e) {
            // Berkas baru yang sudah terunggah tidak dirujuk data mana pun bila penyimpanan gagal.
            foreach ($terunggah as $path) {
                $this->hapus($path, $disk);
            }

            throw $e;
        }

        // File lama baru dihapus setelah data tersimpan agar tidak ada rujukan ke file yang hilang.
        foreach ($berkasLama as $path) {
            $this->hapus($path, $disk);
        }

        return $hasil;
    }

    /**
     * Isi WebP gambar bersisi terpanjang paling banyak SISI_MAKS_GAMBAR, atau null bila berkas asli lebih baik
     * disimpan apa adanya: GD tidak sanggup membukanya atau terlalu besar untuk memori, profil warnanya bukan sRGB,
     * sudah WebP yang tidak perlu diperkecil, atau hasilnya tidak lebih kecil.
     */
    private function webp(UploadedFile $berkas): ?string
    {
        $isi = $berkas->get();
        $ukuran = is_string($isi) && function_exists('imagewebp') ? @getimagesizefromstring($isi) : false;

        if (! is_string($isi) || $ukuran === false || $ukuran[0] * $ukuran[1] > self::PIKSEL_MAKS_GAMBAR || $this->bukanSrgb($isi)) {
            return null;
        }

        [$lebar, $tinggi] = $ukuran;
        $skala = self::SISI_MAKS_GAMBAR / max($lebar, $tinggi, 1);

        // WebP yang tidak perlu diperkecil tidak dikodekan ulang agar mutunya tidak turun. Tanpa ekstensi exif,
        // orientasi foto JPEG tidak terbaca; berkas asli lebih aman daripada foto yang tampil miring.
        if (($skala >= 1 && $ukuran['mime'] === 'image/webp') || ($ukuran['mime'] === 'image/jpeg' && ! function_exists('exif_read_data'))) {
            return null;
        }

        $gambar = @imagecreatefromstring($isi);

        if ($gambar === false) {
            return null;
        }

        if ($skala < 1) {
            $kecil = imagecreatetruecolor(max(1, (int) round($lebar * $skala)), max(1, (int) round($tinggi * $skala)));
            imagealphablending($kecil, false);
            imagecopyresampled($kecil, $gambar, 0, 0, 0, 0, imagesx($kecil), imagesy($kecil), $lebar, $tinggi);
            $gambar = $kecil;
        } else {
            imagepalettetotruecolor($gambar);
        }

        imagesavealpha($gambar, true);
        $gambar = $this->tegakkan($gambar, $ukuran['mime'] === 'image/jpeg' ? $berkas->getRealPath() : false);

        $aliran = fopen('php://memory', 'w+b');

        if ($aliran === false || ! imagewebp($gambar, $aliran, self::KUALITAS_WEBP)) {
            return null;
        }

        rewind($aliran);
        $hasil = stream_get_contents($aliran);
        fclose($aliran);

        return is_string($hasil) && $hasil !== '' && ($skala < 1 || strlen($hasil) < strlen($isi)) ? $hasil : null;
    }

    /**
     * Memutar foto JPEG sesuai tag EXIF Orientation: browser memutar berkas asli menurut tag itu, sedangkan
     * WebP hasil GD tidak membawa EXIF. Diputar setelah diperkecil agar memori GD tetap kecil.
     */
    private function tegakkan(GdImage $gambar, string|false $pathJpeg): GdImage
    {
        $exif = $pathJpeg !== false ? @exif_read_data($pathJpeg) : false;
        $orientasi = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        $diputar = match ($orientasi) {
            3, 4 => imagerotate($gambar, 180, 0),
            5, 6 => imagerotate($gambar, 270, 0),
            7, 8 => imagerotate($gambar, 90, 0),
            default => $gambar,
        };
        $diputar = $diputar === false ? $gambar : $diputar;

        if (in_array($orientasi, [2, 4, 5, 7], true)) {
            imageflip($diputar, IMG_FLIP_HORIZONTAL);
        }

        return $diputar;
    }

    /**
     * Profil warna tertanam (ICC) selain sRGB, mis. Display P3 dari iPhone/Mac: GD membuang profil saat mengodekan
     * ulang sehingga warnanya bergeser. Profil yang tidak terbaca dianggap bukan sRGB.
     */
    private function bukanSrgb(string $isi): bool
    {
        $panjang = fn (string $format, int $posisi): int => (int) (unpack($format, substr($isi, $posisi, 4)) ?: [1 => 0])[1];

        if (str_starts_with($isi, "\xFF\xD8") && ($posisi = strpos($isi, "ICC_PROFILE\0")) !== false) {
            $profil = substr($isi, $posisi, 65535);
        } elseif (str_starts_with($isi, "\x89PNG") && ($posisi = strpos($isi, 'iCCP')) !== false) {
            // Data chunk iCCP: nama profil, \0, metode kompresi (1 byte), lalu profil terkompresi zlib.
            $data = substr($isi, $posisi + 4, $panjang('N', $posisi - 4));
            $akhirNama = strpos($data, "\0");
            $profil = $akhirNama === false ? false : @gzuncompress(substr($data, $akhirNama + 2));
        } elseif (str_starts_with($isi, 'RIFF') && substr($isi, 30, 4) === 'ICCP') {
            // Chunk ICCP WebP wajib tepat setelah chunk VP8X (offset 30).
            $profil = substr($isi, 38, $panjang('V', 34));
        } else {
            return false;
        }

        // Nama profil sRGB tertulis ASCII (ICC v2) atau UTF-16 (ICC v4).
        return ! is_string($profil) || ! (str_contains($profil, 'sRGB') || str_contains($profil, "s\0R\0G\0B"));
    }
}
