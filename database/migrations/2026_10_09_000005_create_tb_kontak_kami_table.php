<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_kontak_kami', function (Blueprint $table) {
            $table->id('id_kontak_kami');
            $table->string('nama', 100);
            $table->string('email', 150);
            $table->string('subjek', 200);
            $table->text('pesan');
            $table->dateTime('tanggal');
            $table->boolean('status_baca')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_kontak_kami');
    }
};
