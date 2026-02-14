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
        // Use raw SQL for dropping constraints to handle potential missing or existing constraints robustly
        try {
            DB::statement('ALTER TABLE spec_rab_detail DROP FOREIGN KEY spec_rab_detail_id_spec_foreign');
        } catch (\Exception $e) {
            // Ignore if FK doesn't exist
        }

        try {
            // Also try to drop the index for the foreign key, just in case it's separate
            DB::statement('DROP INDEX spec_rab_detail_id_spec_foreign ON spec_rab_detail');
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }

        try {
            DB::statement('ALTER TABLE spec_rab_detail DROP PRIMARY KEY');
        } catch (\Exception $e) {
            // Ignore if PK doesn't exist
        }

        Schema::table('spec_rab_detail', function (Blueprint $table) {
            // Set new Primary Key
            $table->primary('cost_element');

            // Restore Foreign Key
            $table->foreign('id_spec')
                  ->references('id_spec')
                  ->on('spec_rab')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE spec_rab_detail DROP PRIMARY KEY');
        } catch (\Exception $e) {
            // Ignore
        }

        Schema::table('spec_rab_detail', function (Blueprint $table) {
            $table->primary(['id_spec', 'cost_element']);
        });
    }
};
