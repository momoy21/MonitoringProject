<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_divisi', function (Blueprint $table) {
            $table->string('kode_divisi', 10)->primary(); 
            $table->string('nama_divisi', 100)->nullable(); 
            $table->char('status', 1)->nullable()->default('A'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_divisi');
    }
};