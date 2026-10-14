<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_template', function (Blueprint $table) {
            $table->id('id_template');
            $table->string('nama', 100);
            $table->string('jenis', 20);
            // Kolom yang tidak relevan dengan jenisnya disimpan NULL: warna untuk Full Color, nada dasar untuk Monochrome.
            $table->char('warna_utama', 7)->nullable();
            $table->char('warna_aksen', 7)->nullable();
            $table->string('nada_dasar', 20)->nullable();
            $table->string('font', 50);
            $table->string('sudut', 20);
            $table->string('skala', 20);
            $table->string('lebar_konten', 20);
            $table->string('ketebalan_judul', 20);
            $table->string('bayangan', 20);
            $table->boolean('status_aktif')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Setiap halaman publik mencari template aktif per jenis.
            $table->index(['jenis', 'status_aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_template');
    }
};
