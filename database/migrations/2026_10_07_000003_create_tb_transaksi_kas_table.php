<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_transaksi_kas', function (Blueprint $table) {
            $table->id('id_transaksi_kas');
            $table->string('nomor_transaksi', 30)->unique();
            $table->foreignId('id_akun_kas')
                ->constrained('tb_akun_kas', 'id_akun_kas')
                ->restrictOnDelete();
            $table->foreignId('id_kategori_transaksi')
                ->constrained('tb_kategori_transaksi', 'id_kategori_transaksi')
                ->restrictOnDelete();
            $table->string('jenis_transaksi', 20);
            $table->date('tanggal_transaksi');
            $table->decimal('jumlah', 15, 2);
            $table->string('nama_pihak', 150)->nullable();
            $table->text('keterangan');
            $table->string('bukti_transaksi', 255)->nullable();
            $table->nullableMorphs('referensi');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('tb_pengguna', 'id_pengguna')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('tb_pengguna', 'id_pengguna')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal_transaksi');
            $table->index(['id_akun_kas', 'tanggal_transaksi']);
            $table->index(['jenis_transaksi', 'tanggal_transaksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_transaksi_kas');
    }
};
