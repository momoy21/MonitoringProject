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
        Schema::table('history_proyek', function (Blueprint $table) {
            // Drop old index on cost_center (used for old grouping logic)
            $table->dropIndex(['cost_center']);

            // Add new index on id_project (new grouping logic)
            // Each id_project groups its own history records
            $table->index('id_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_proyek', function (Blueprint $table) {
            // Revert: drop id_project index
            $table->dropIndex(['id_project']);

            // Restore old cost_center index
            $table->index('cost_center');
        });
    }
};
