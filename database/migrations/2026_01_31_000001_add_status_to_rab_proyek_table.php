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
        Schema::table('rab_proyek', function (Blueprint $table) {
            $table->string('status', 1)->nullable()->default('D')->after('jenis_proyek')
                  ->comment('D = Draft RAB, F = Final RAB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rab_proyek', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
