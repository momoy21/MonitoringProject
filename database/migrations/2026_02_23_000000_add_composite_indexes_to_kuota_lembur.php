<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite index on (cost_center, nik, bulan) and (cost_center, nik, periode_awal)
     * to speed up getData, store duplicate check, and getNextBulan queries.
     */
    public function up(): void
    {
        Schema::table('kuota_lembur', function (Blueprint $table) {
            // Speeds up: getData query, getNextBulan, store duplicate check
            $table->index(['cost_center', 'nik', 'bulan'], 'idx_kuota_cc_nik_bulan');
            $table->index(['cost_center', 'nik', 'periode_awal'], 'idx_kuota_cc_nik_pawal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuota_lembur', function (Blueprint $table) {
            $table->dropIndex('idx_kuota_cc_nik_bulan');
            $table->dropIndex('idx_kuota_cc_nik_pawal');
        });
    }
};
