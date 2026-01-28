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
        Schema::create('issue_proyek', function (Blueprint $table) {
            // Composite Primary Key
            $table->integer('norut')->comment('From history_proyek.norut');
            $table->string('id_project', 10)->comment('From history_proyek.id_project');
            $table->string('no_issue', 5)->comment('Format: IS + 001, sequential per project+norut');

            // Fields
            $table->dateTime('tanggal')->nullable()->comment('Tanggal issue dibuat');
            $table->text('issue')->nullable()->default('Tidak ada issue')->comment('Deskripsi issue/kendala');
            $table->text('mitigasi')->nullable()->default('Tidak ada mitigasi')->comment('Mitigasi issue');
            $table->char('status', 1)->nullable()->default('O')->comment('O=Open, C=Close');

            $table->timestamps();

            // Composite Primary Key
            $table->primary(['norut', 'id_project', 'no_issue']);

            // Foreign key to history_proyek
            $table->foreign(['norut', 'id_project'])
                  ->references(['norut', 'id_project'])
                  ->on('history_proyek')
                  ->onDelete('cascade');

            // Index
            $table->index(['id_project', 'norut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_proyek');
    }
};
