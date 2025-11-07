<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bidangjasa', function (Blueprint $table) {
            $table->string('id_bidjasa', 2)->primary()->comment('ID Bidang Jasa 2 digit');
            $table->string('desc_bidjasa', 50)->nullable(false)->comment('Nama Bidang Jasa');
            $table->timestamps();
        });

        DB::table('bidangjasa')->insert([
            ['id_bidjasa' => '01', 'desc_bidjasa' => 'SAP'],
            ['id_bidjasa' => '02', 'desc_bidjasa' => 'Pengelolaan System'],
            ['id_bidjasa' => '03', 'desc_bidjasa' => 'Hospital System'],
            ['id_bidjasa' => '04', 'desc_bidjasa' => 'Manufacture System'],
            ['id_bidjasa' => '05', 'desc_bidjasa' => 'Infrastructure IT'],
            ['id_bidjasa' => '06', 'desc_bidjasa' => 'Control & Auto'],
            ['id_bidjasa' => '07', 'desc_bidjasa' => 'Electrical and Instrument'],
            ['id_bidjasa' => '08', 'desc_bidjasa' => 'Prospek'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bidangjasa');
    }
};
