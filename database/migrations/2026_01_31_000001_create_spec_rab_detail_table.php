<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel Spesifikasi RAB Detail (specRABdetail)
     * Menyimpan mapping antara Spesifikasi RAB dengan Cost Element
     */
    public function up(): void
    {
        Schema::create('spec_rab_detail', function (Blueprint $table) {
            // ID Spesifikasi RAB - FK ke tabel spec_rab
            $table->string('id_spec', 10)->comment('ID Spesifikasi RAB, FK ke spec_rab');
            
            // Cost Element - kode cost element dari SAP
            $table->string('cost_element', 10)->comment('Kode Cost Element');
            
            // Description Cost Element (optional)
            $table->text('description_ce')->nullable()->comment('Deskripsi Cost Element');
            
            $table->timestamps();

            // Composite Primary Key
            $table->primary(['id_spec', 'cost_element']);

            // Foreign Key ke spec_rab
            $table->foreign('id_spec')
                  ->references('id_spec')
                  ->on('spec_rab')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Index untuk pencarian
            $table->index('cost_element');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spec_rab_detail');
    }
};
