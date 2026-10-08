<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_faq', function (Blueprint $table) {
            $table->id('id_faq');
            $table->string('pertanyaan', 255);
            $table->text('jawaban');
            $table->unsignedSmallInteger('urutan_ke')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_faq');
    }
};
