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
        Schema::create('berita_acara_project', function (Blueprint $table) {
            // Composite Primary Key
            $table->string('norut', 2)->comment('Auto-generate, reset per id_project');
            $table->string('id_project', 10)->comment('10 digit: YYYY(4) + MM(2) + 0001(4)');
            $table->string('no_ba', 9)->comment('Format: BA + YYYY(4) + 001(3), reset per project+norut');

            // Fields
            $table->text('desc')->nullable()->comment('Keterangan milestone/BA');
            $table->date('periode_mulai')->nullable()->comment('Periode mulai BA');
            $table->date('periode_akhir')->nullable()->comment('Periode akhir BA');
            $table->decimal('nilai_ba', 16, 2)->nullable()->comment('Nilai pendapatan BA');
            $table->char('status', 2)->nullable()->comment('01=Draft, 02=Review, 03=Approve, 04=Pending');

            $table->timestamps();

            // Composite Primary Key
            $table->primary(['norut', 'id_project', 'no_ba']);

            // Foreign key to history_proyek
            $table->index(['id_project', 'norut']);

            // Table comment
            $table->comment('Tabel berita acara project untuk tracking milestone dan nilai BA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acara_project');
    }
};
