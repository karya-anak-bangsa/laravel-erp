<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peta lokasi di frontend memakai Leaflet, bukan sematan Google Maps, sehingga
 * link_gmap tidak dipakai lagi (keputusan pemilik). Migration baru, bukan mengubah
 * create_tb_identitas_table, karena tabel itu sudah ada di produksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_identitas', function (Blueprint $table) {
            $table->dropColumn('link_gmap');
        });
    }

    public function down(): void
    {
        Schema::table('tb_identitas', function (Blueprint $table) {
            $table->text('link_gmap')->nullable()->after('alamat');
        });
    }
};
