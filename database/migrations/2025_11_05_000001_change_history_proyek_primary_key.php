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
            // Drop the auto-increment id column
            $table->dropColumn('id');
        });

        Schema::table('history_proyek', function (Blueprint $table) {
            // Set composite primary key (norut, id_project)
            $table->primary(['norut', 'id_project']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_proyek', function (Blueprint $table) {
            // Drop composite primary key
            $table->dropPrimary(['norut', 'id_project']);
        });

        Schema::table('history_proyek', function (Blueprint $table) {
            // Restore the auto-increment id column
            $table->id()->first();
        });
    }
};
