<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_materi_tutorial', function (Blueprint $table) {
            $table->id('id_materi_tutorial');
            $table->foreignId('id_tutorial')
                ->constrained('tb_tutorial', 'id_tutorial')
                ->restrictOnDelete();
            $table->string('judul', 200);
            // Alamat materi memuat slug tutorialnya, jadi cukup unik per tutorial (termasuk baris terhapus).
            $table->string('slug', 220);
            $table->longText('deskripsi');
            $table->unsignedSmallInteger('urutan_ke')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id_tutorial', 'slug']);
            $table->index(['id_tutorial', 'urutan_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_materi_tutorial');
    }
};
