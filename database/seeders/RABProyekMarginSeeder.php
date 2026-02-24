<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RABProyekMarginSeeder extends Seeder
{
    /**
     * Seed varied margin_rkap and margin_pleno values for dashboard testing.
     * Includes negative margins to populate "Project Rugi" KPI.
     */
    public function run(): void
    {
        $marginPairs = [
            // [rkap, pleno] — mix of positive, negative, and edge cases
            [15, 18],
            [20, 12],
            [10, -2],
            [25, 30],
            [8, 3],
            [12, -8],
            [18, 22],
            [5, -5],
            [30, 25],
            [7, 4],
            [-3, -10],
            [22, 28],
            [14, 9],
            [10, 15],
            [16, -4],
            [9, 11],
            [28, 20],
            [6, -1],
            [11, 13],
            [19, 17],
        ];

        $rows = DB::table('rab_proyek')->select('nopengajuan')->get();
        $count = count($marginPairs);
        $updated = 0;

        foreach ($rows as $i => $row) {
            $pair = $marginPairs[$i % $count];
            // Add some randomness within ±3 range
            $rkap = $pair[0] + (rand(-30, 30) / 10);
            $pleno = $pair[1] + (rand(-30, 30) / 10);

            DB::table('rab_proyek')
                ->where('nopengajuan', $row->nopengajuan)
                ->update([
                    'margin_rkap' => round($rkap, 2),
                    'margin_pleno' => round($pleno, 2),
                ]);
            $updated++;
        }

        $this->command->info("Updated margins for {$updated} rab_proyek records.");
    }
}
