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
        Schema::create('master_manager', function (Blueprint $table) {
            $table->string('nik', 7)->primary()->comment('NIK Manager 7 karakter');
            $table->string('nama', 100)->nullable()->comment('Nama Manager');
            $table->char('status', 1)->nullable()->comment('Status: A=Aktif, N=Non Aktif');
            $table->timestamps();

            // Index
            $table->index(['nama']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_manager');
    }
};
