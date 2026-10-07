<?php

namespace App\Services\Shared;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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
}
