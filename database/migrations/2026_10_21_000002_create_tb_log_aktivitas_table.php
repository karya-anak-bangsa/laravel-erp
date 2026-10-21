<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya ditambah, tidak pernah diubah atau dihapus dari aplikasi, jadi tanpa updated_at dan deleted_at.
        Schema::create('tb_log_aktivitas', function (Blueprint $table) {
            $table->id('id_log_aktivitas');
            // Akun pengguna hanya soft delete, jadi pelaku selalu bisa ditelusuri.
            $table->foreignId('id_pengguna')
                ->constrained('tb_pengguna', 'id_pengguna')
                ->restrictOnDelete();
            $table->string('aksi', 20)->index();
            // Alias morph map (nama tabel tanpa tb_); kosong untuk masuk & keluar.
            $table->nullableMorphs('subjek');
            // Nama data saat aksi terjadi, tetap terbaca walau data kemudian diganti nama atau dihapus.
            $table->string('label_subjek', 255)->nullable();
            $table->json('perubahan')->nullable();
            $table->string('alamat_ip', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_log_aktivitas');
    }
};
