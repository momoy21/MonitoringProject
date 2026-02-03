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
        Schema::table('spec_rab_detail', function (Blueprint $table) {
            $table->text('description_ce')->nullable()->comment('Deskripsi Cost Element')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spec_rab_detail', function (Blueprint $table) {
            $table->text('description_ce')->nullable(false)->comment('Deskripsi Cost Element')->change();
        });
    }
};
