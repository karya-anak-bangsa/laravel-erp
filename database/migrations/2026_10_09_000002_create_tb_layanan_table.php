<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->text('keterangan')->nullable();
            $table->unsignedSmallInteger('urutan_ke')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_layanan');
    }
};
