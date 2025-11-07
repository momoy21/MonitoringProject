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
        Schema::create('header_rab', function (Blueprint $table) {
            $table->string('id_rab', 10)->primary()->comment('Format: YYYYMM0001');
            $table->string('id_project', 10)->nullable();
            $table->string('norut', 2)->nullable();
            $table->date('periode_rab')->nullable()->comment('Tanggal mulai RAB');
            $table->integer('lama')->nullable()->comment('Lama periode RAB dalam bulan');
            $table->timestamps();

            // Foreign key constraints
            $table->index(['id_project', 'norut']);

            // Add comment for table
            $table->comment('Table untuk menyimpan header RAB yang terhubung dengan history proyek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_rab');
    }
};
