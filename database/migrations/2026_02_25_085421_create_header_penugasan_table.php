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
        Schema::create('header_penugasan', function (Blueprint $table) {
            // Composite PK sesuai spesifikasi
            $table->string('IDPenugasan', 10);
            $table->string('cost_center', 9);
            $table->string('PejabatTandatangan', 30)->nullable();

            $table->timestamps();

            // Primary Key komposit
            $table->primary(['IDPenugasan', 'cost_center']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_penugasan');
    }
};
