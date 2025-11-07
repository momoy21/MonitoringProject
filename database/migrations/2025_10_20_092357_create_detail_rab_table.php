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
        Schema::create('detail_rab', function (Blueprint $table) {
            $table->string('id_rab', 10)->comment('Foreign key dari header_rab');
            $table->integer('id_detail_rab')->comment('Primary key - nomor urut unik untuk setiap rincian dalam satu dokumen RAB');
            $table->string('id_spec', 4)->comment('Foreign key dari spec_rab table');
            $table->string('bulan', 10)->comment('Format mmm/yyyy - bulan dalam RAB');
            $table->integer('urutbln')->comment('Urutan bulan RAB dimulai dari 0');
            $table->decimal('nilai', 18, 2)->nullable()->comment('Nilai dari file Excel');
            $table->timestamps();

            // Set primary key sebagai composite key
            $table->primary(['id_rab', 'id_detail_rab']);

            // Foreign key constraints
            $table->foreign('id_rab')->references('id_rab')->on('header_rab')->onDelete('cascade');
            $table->foreign('id_spec')->references('idspec')->on('spec_rab')->onDelete('restrict');

            // Index untuk performa
            $table->index(['id_rab', 'urutbln']);
            $table->index(['id_spec']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rab');
    }
};
