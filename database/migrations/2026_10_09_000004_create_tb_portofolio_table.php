<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_portofolio', function (Blueprint $table) {
            $table->id('id_portofolio');
            $table->foreignId('id_kategori_portofolio')
                ->constrained('tb_kategori_portofolio', 'id_kategori_portofolio')
                ->restrictOnDelete();
            $table->string('judul', 200);
            // Unik termasuk data terhapus: dibuat sistem dan menjadi URL detail di frontend.
            $table->string('slug', 220)->unique();
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_portofolio');
    }
};
