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
        // Update existing records to have 'N' status if they don't have a valid status
        DB::table('data_peluang')
            ->whereNotIn('status', ['I', 'D', 'C'])
            ->orWhereNull('status')
            ->update(['status' => 'N']);

        // Set default status for new records
        Schema::table('data_peluang', function (Blueprint $table) {
            $table->char('status', 1)->default('N')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert default status
        Schema::table('data_peluang', function (Blueprint $table) {
            $table->char('status', 1)->default('I')->change();
        });

        // Convert 'N' status back to 'I'
        DB::table('data_peluang')
            ->where('status', 'N')
            ->update(['status' => 'I']);
    }
};
