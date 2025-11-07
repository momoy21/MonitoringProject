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
        // Drop foreign key constraint first
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->dropForeign(['id_spec']);
        });

        // Rename column in spec_rab table
        Schema::table('spec_rab', function (Blueprint $table) {
            $table->renameColumn('idspec', 'id_spec');
        });

        // Re-add foreign key constraint with correct reference
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->foreign('id_spec')->references('id_spec')->on('spec_rab')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint first
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->dropForeign(['id_spec']);
        });

        // Rename column back in spec_rab table
        Schema::table('spec_rab', function (Blueprint $table) {
            $table->renameColumn('id_spec', 'idspec');
        });

        // Re-add foreign key constraint with old reference
        Schema::table('detail_rab', function (Blueprint $table) {
            $table->foreign('id_spec')->references('idspec')->on('spec_rab')->onDelete('restrict');
        });
    }
};
