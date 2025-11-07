<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_peluang', function (Blueprint $table) {
            $table->string('id_datapeluang', 4)->primary();
            $table->text('peluang');
            $table->string('id_konsumen', 6);
            $table->string('kontak_person', 100)->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('lokasi', 100)->nullable();
            $table->date('tgl_peluang');
            $table->date('target_peluang');
            $table->decimal('biaya_peluang', 18, 2)->nullable();
            $table->decimal('pagu_peluang', 18, 2)->nullable();
            $table->char('status', 1)->default('I');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('id_konsumen')->references('id_konsumen')->on('konsumen')->onDelete('cascade');

            // Indexes
            $table->index(['status']);
            $table->index(['tgl_peluang']);
            $table->index(['target_peluang']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_peluang');
    }
};
