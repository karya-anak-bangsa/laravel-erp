<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_identitas', function (Blueprint $table) {
            $table->id('id_identitas');
            $table->string('nama_perusahaan', 150);
            $table->string('judul_website', 150);
            $table->string('alamat_website', 255);
            $table->string('meta_deskripsi', 255)->nullable();
            $table->string('meta_keyword', 255)->nullable();
            $table->string('logo_website', 255);
            $table->string('favicon_website', 255);
            $table->string('email', 150);
            $table->string('telepon', 30);
            $table->text('alamat');
            $table->text('link_gmap')->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('link_instagram', 255)->nullable();
            $table->string('link_whatsapp', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_identitas');
    }
};
