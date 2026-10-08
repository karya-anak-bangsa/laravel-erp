<?php

namespace Database\Seeders\Concerns;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

trait MenyalinBerkasAwal
{
    /**
     * Menyalin berkas bawaan di folder public ke disk penyimpanan dengan nama UUID,
     * agar data awal punya berkas yang bisa diganti/dihapus seperti unggahan biasa.
     */
    protected function salinBerkasAwal(string $pathPublik, string $folder, string $disk): string
    {
        $path = Storage::disk($disk)->putFileAs(
            $folder,
            new File(public_path($pathPublik)),
            Str::uuid().'.'.pathinfo($pathPublik, PATHINFO_EXTENSION),
        );

        if ($path === false) {
            throw new RuntimeException("Berkas awal {$pathPublik} gagal disalin.");
        }

        return $path;
    }
}
