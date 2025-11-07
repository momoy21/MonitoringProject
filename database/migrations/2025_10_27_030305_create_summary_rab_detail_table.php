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
        Schema::create('summary_rab_detail', function (Blueprint $table) {
            $table->string('id_rab', 10)->comment('Foreign key dari header_rab');
            $table->integer('id_summary_rab')->comment('Primary key - nomor urut unik untuk setiap summary dalam satu dokumen RAB');
            $table->string('idsummary', 4)->comment('Foreign key dari summary_rab table');
            $table->decimal('nilai', 18, 2)->nullable()->comment('Nilai dari file Excel kolom F baris 30+');
            $table->timestamps();

            // Set composite primary key
            $table->primary(['id_rab', 'id_summary_rab']);

            // Foreign key constraints
            $table->foreign('id_rab')->references('id_rab')->on('header_rab')->onDelete('cascade');
            $table->foreign('idsummary')->references('idsummary')->on('summary_rab')->onDelete('restrict');

            // Index untuk performa
            $table->index(['id_rab']);
            $table->index(['idsummary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_rab_detail');
    }
};
