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
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->string('NoSurat', 50)->nullable()->after('cost_center');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_penugasan', function (Blueprint $table) {
            $table->dropColumn('NoSurat');
        });
    }
};
