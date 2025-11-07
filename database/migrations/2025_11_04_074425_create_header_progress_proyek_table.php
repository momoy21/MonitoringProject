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
        Schema::create('header_progress_proyek', function (Blueprint $table) {
            $table->string('id_progress', 10)->primary()->comment('Format: YYYYMM0001');
            $table->string('id_rab', 10)->nullable();
            $table->string('id_project', 10)->nullable();
            $table->string('norut', 2)->nullable();
            $table->date('periode_mulai')->nullable()->comment('Tanggal mulai progress dari header RAB');
            $table->integer('lama')->nullable()->comment('Lama periode dalam bulan dari header RAB');
            $table->date('periode_akhir')->nullable()->comment('Tanggal akhir progress (dihitung otomatis)');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_rab')->references('id_rab')->on('header_rab')->onDelete('cascade');
            $table->index(['id_project', 'norut']);

            // Add comment for table
            $table->comment('Table untuk menyimpan header progress proyek yang terhubung dengan header RAB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_progress_proyek');
    }
};
