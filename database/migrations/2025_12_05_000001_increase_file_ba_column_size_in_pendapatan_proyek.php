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
            // Increase file_ba column size from 100 to 255
            $table->string('file_ba', 255)->nullable()->comment('File dokumen berita acara')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendapatan_proyek', function (Blueprint $table) {
            // Revert back to 100
            $table->string('file_ba', 100)->nullable()->comment('File dokumen berita acara')->change();
        });
    }
};
