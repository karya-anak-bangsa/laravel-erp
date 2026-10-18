<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tanpa softDeletes: gambar tambahan bagian dari portofolio, seperti kolom gambar (docs/DATABASE.md).
        Schema::create('tb_gambar_portofolio', function (Blueprint $table) {
            $table->id('id_gambar_portofolio');
            $table->foreignId('id_portofolio')
                ->constrained('tb_portofolio', 'id_portofolio')
                ->cascadeOnDelete();
            $table->string('gambar', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_gambar_portofolio');
    }
};
