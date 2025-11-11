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
        Schema::table('pendapatan_proyek', function (Blueprint $table) {
            // Make no_ba and tanggal NOT NULL (required)
            $table->string('no_ba', 9)->nullable(false)->change();
            $table->date('tanggal')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendapatan_proyek', function (Blueprint $table) {
            // Revert back to nullable
            $table->string('no_ba', 9)->nullable()->change();
            $table->date('tanggal')->nullable()->change();
        });
    }
};
