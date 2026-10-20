<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_tutorial', function (Blueprint $table) {
            $table->id('id_tutorial');
            $table->foreignId('id_kategori_tutorial')
                ->constrained('tb_kategori_tutorial', 'id_kategori_tutorial')
                ->restrictOnDelete();
            $table->string('judul', 200);
            // Dibuat sistem, jadi UNIQUE di database termasuk baris terhapus (sama seperti slug artikel).
            $table->string('slug', 220)->unique();
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->string('status_publikasi', 20)->default('draf');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_tutorial');
    }
};
