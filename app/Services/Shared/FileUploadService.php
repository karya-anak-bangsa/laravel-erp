<?php

namespace App\Services\Shared;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FileUploadService
{
    /**
     * Simpan berkas dengan nama UUID; mengembalikan path relatif terhadap disk.
     */
    public function simpan(UploadedFile $berkas, string $folder, string $disk): string
    {
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
     * dari data sehingga path lama tetap dipakai.
     *
     * @template TModel of Model
     *
     * @param  TModel|null  $model  model yang diubah (null saat tambah); berkas lamanya dihapus setelah sukses
     * @param  array<string, mixed>  $data  hasil validasi
     * @param  list<string>  $kolomBerkas
     * @param  Closure(array<string, mixed>): TModel  $simpan
     * @return TModel
     */
    public function simpanDenganBerkas(?Model $model, array $data, array $kolomBerkas, string $folder, string $disk, Closure $simpan): Model
    {
        $berkasBaru = [];
        $berkasLama = [];

        try {
            foreach ($kolomBerkas as $kolom) {
                $berkas = $data[$kolom] ?? null;
                unset($data[$kolom]);

                if ($berkas instanceof UploadedFile) {
                    $berkasBaru[$kolom] = $this->simpan($berkas, $folder, $disk);
                    $lama = $model?->getAttribute($kolom);

                    if (is_string($lama)) {
                        $berkasLama[] = $lama;
                    }
                }
            }

            $hasil = $simpan([...$data, ...$berkasBaru]);
        } catch (Throwable $e) {
            // Berkas baru yang sudah terunggah tidak dirujuk data mana pun bila penyimpanan gagal.
            foreach ($berkasBaru as $path) {
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
}
