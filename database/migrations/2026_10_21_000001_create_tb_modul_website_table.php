<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_modul_website', function (Blueprint $table) {
            $table->id('id_modul_website');
            // Satu baris per modul (enum JenisModul), dibuat ModulWebsiteSeeder; admin hanya mengubah status.
            $table->string('jenis', 20)->unique();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_modul_website');
    }
};
