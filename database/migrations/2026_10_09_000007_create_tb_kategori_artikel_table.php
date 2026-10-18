<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_kategori_artikel', function (Blueprint $table) {
            $table->id('id_kategori_artikel');
            $table->string('nama_kategori', 100);
            // Dibuat sistem, jadi UNIQUE di database termasuk baris terhapus (sama seperti slug artikel).
            $table->string('slug', 220)->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_kategori_artikel');
    }
};
