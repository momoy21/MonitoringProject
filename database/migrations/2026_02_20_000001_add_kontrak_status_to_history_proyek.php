<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom kontrak_status untuk tracking status kontrak:
     * - A: Akan Berakhir (H-30)
     * - B: Berakhir (Expired)
     * - NULL: Normal/Active
     */
    public function up(): void
    {
        Schema::table('history_proyek', function (Blueprint $table) {
            $table->char('kontrak_status', 1)->nullable()->after('status')
                  ->comment('A=Akan Berakhir, B=Berakhir');
        });

        // Also add to data_proyek for main project tracking
        Schema::table('data_proyek', function (Blueprint $table) {
            $table->char('kontrak_status', 1)->nullable()->after('status')
                  ->comment('A=Akan Berakhir, B=Berakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_proyek', function (Blueprint $table) {
            $table->dropColumn('kontrak_status');
        });

        Schema::table('data_proyek', function (Blueprint $table) {
            $table->dropColumn('kontrak_status');
        });
    }
};
