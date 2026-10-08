<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_hero', function (Blueprint $table) {
            $table->id('id_hero');
            $table->string('judul', 200);
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->json('keyword');
            $table->json('cta');
            // Frontend mencari hero yang aktif, jadi kolom ini diindeks.
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_hero');
    }
};
