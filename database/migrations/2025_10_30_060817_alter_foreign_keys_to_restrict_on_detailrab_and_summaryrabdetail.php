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
        // DETAIL RAB: drop and re-add foreign key to spec_rab with RESTRICT
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->dropForeign(['id_spec']);
            $table->foreign('id_spec')->references('id_spec')->on('spec_rab')->onDelete('restrict');
        });

        // SUMMARY RAB DETAIL: drop and re-add foreign key to summary_rab with RESTRICT
        Schema::table('summary_rab_detail', function (Blueprint $table) {
            $table->dropForeign(['idsummary']);
            $table->foreign('idsummary')->references('idsummary')->on('summary_rab')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DETAIL RAB: revert to CASCADE (or NO ACTION if needed)
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->dropForeign(['id_spec']);
            $table->foreign('id_spec')->references('id_spec')->on('spec_rab')->onDelete('cascade');
        });

        // SUMMARY RAB DETAIL: revert to CASCADE (or NO ACTION if needed)
        Schema::table('summary_rab_detail', function (Blueprint $table) {
            $table->dropForeign(['idsummary']);
            $table->foreign('idsummary')->references('idsummary')->on('summary_rab')->onDelete('cascade');
        });
    }
};
