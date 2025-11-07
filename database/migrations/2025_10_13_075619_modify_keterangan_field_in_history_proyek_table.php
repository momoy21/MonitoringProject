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
            // Ubah keterangan dari char(1) menjadi varchar(255) untuk field bebas
            $table->string('keterangan', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_proyek', function (Blueprint $table) {
            // Kembalikan ke char(1)
            $table->char('keterangan', 1)->nullable()->change();
        });
    }
};
