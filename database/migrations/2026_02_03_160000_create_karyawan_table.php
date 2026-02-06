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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->string('nik', 9)->primary()->comment('Nomor Induk Karyawan');
            $table->string('nama', 100)->comment('Nama Karyawan');
            $table->string('status', 1)->comment('Status: T=Tetap, K=Kontrak, J=JO');
            $table->string('aktif', 1)->default('Y')->comment('Aktif: Y=Ya, T=Tidak');
            $table->timestamps();

            // Index untuk pencarian berdasarkan nama
            $table->index('nama');
            $table->index('status');
            $table->index('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
