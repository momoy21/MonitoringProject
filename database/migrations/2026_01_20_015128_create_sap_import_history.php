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
        Schema::create('sap_import_history', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('file_hash', 32)->index()->comment('MD5 hash untuk deteksi duplikat');
            $table->bigInteger('file_size')->default(0);
            $table->integer('record_count')->default(0);
            $table->string('status', 50)->default('PENDING')->comment('PENDING, SUCCESS, FAILED, DUPLICATE');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index('filename');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_import_history');
    }
};