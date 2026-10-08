<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_artikel', function (Blueprint $table) {
            $table->id('id_artikel');
            $table->foreignId('id_kategori_artikel')
                ->constrained('tb_kategori_artikel', 'id_kategori_artikel')
                ->restrictOnDelete();
            $table->string('judul', 200);
            // Dibuat sistem, jadi UNIQUE di database termasuk baris terhapus (sama seperti portofolio).
            $table->string('slug', 220)->unique();
            $table->longText('deskripsi');
            $table->string('gambar', 255);
            $table->date('tanggal')->index();
            $table->string('status_publikasi', 20)->default('draf');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_artikel');
    }
};
