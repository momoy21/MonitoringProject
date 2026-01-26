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
        Schema::create('plsap', function (Blueprint $table) {
            $table->id();
            $table->string('internal_order', 12)->nullable()->comment('Internal Order dari SAP');
            $table->string('cc_projek', 20)->nullable()->comment('Cost Center Projek');
            $table->string('description_io', 255)->nullable()->comment('Description Internal Order');
            $table->string('cost_element', 20)->nullable()->comment('Cost Element');
            $table->string('description_ce', 255)->nullable()->comment('Description Cost Element');
            $table->decimal('amount_local', 18, 2)->default(0)->comment('Amount dalam Rupiah');
            $table->date('posting_date')->nullable()->comment('Tanggal Posting');
            $table->string('profit_center', 20)->nullable()->comment('Profit Center');
            $table->string('description_pca', 255)->nullable()->comment('Description Profit Center');
            $table->string('source_file', 255)->nullable()->comment('Nama file sumber');
            $table->timestamp('imported_at')->nullable()->comment('Waktu import');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index('internal_order');
            $table->index('cc_projek');
            $table->index('posting_date');
            $table->index('cost_element');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plsap');
    }
};