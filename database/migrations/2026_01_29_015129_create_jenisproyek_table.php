<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenisproyek', function (Blueprint $table) {
            // Kita gunakan string karena di Model Anda set $keyType = 'string'
            // dan id-nya memakai format 'P1', 'P2', dst.
            $table->string('idjenisproyek', 10)->primary(); 
            $table->string('jenisproyek', 100);
            $table->enum('status', ['A', 'N'])->default('A'); // A=Aktif, N=Non-Aktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenisproyek');
    }
};