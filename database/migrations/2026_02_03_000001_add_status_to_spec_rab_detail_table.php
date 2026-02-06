<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom status ke tabel spec_rab_detail
     * A = Aktif, N = Non Aktif
     */
    public function up(): void
    {
        Schema::table('spec_rab_detail', function (Blueprint $table) {
            $table->char('status', 1)->default('A')->after('description_ce')
                  ->comment('Status: A=Aktif, N=Non Aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spec_rab_detail', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
