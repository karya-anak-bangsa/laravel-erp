<?php

namespace Database\Seeders\Concerns;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

trait MenyalinBerkasAwal
{
    /**
     * Menyalin berkas bawaan (path lengkap, mis. public_path('favicon.png')) ke disk
     * penyimpanan dengan nama UUID, agar data awal punya berkas yang bisa diganti/dihapus
     * seperti unggahan biasa.
     */
    protected function salinBerkasAwal(string $pathSumber, string $folder, string $disk): string
    {
        $path = Storage::disk($disk)->putFileAs(
            $folder,
            new File($pathSumber),
            Str::uuid().'.'.pathinfo($pathSumber, PATHINFO_EXTENSION),
        );

        if ($path === false) {
            throw new RuntimeException("Berkas awal {$pathSumber} gagal disalin.");
        }

        return $path;
    }
}
