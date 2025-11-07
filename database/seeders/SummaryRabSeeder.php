<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SummaryRabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $summaryRAB = [
            [
                'idsummary' => '0001',
                'ketsummaryrab' => 'Total Biaya',
                'norutsummary' => '01',
                'status' => 'A'
            ],
            [
                'idsummary' => '0002',
                'ketsummaryrab' => 'Porsi Laba',
                'norutsummary' => '02',
                'status' => 'A'
            ],
            [
                'idsummary' => '0003',
                'ketsummaryrab' => 'Dana Insentif Proyek',
                'norutsummary' => '03',
                'status' => 'A'
            ],
            [
                'idsummary' => '0004',
                'ketsummaryrab' => 'Data Insentif Sales',
                'norutsummary' => '04',
                'status' => 'A'
            ],
            [
                'idsummary' => '0005',
                'ketsummaryrab' => 'Porsi SBU & OVH',
                'norutsummary' => '05',
                'status' => 'A'
            ],
            [
                'idsummary' => '0006',
                'ketsummaryrab' => 'Kemenangan Penjualan',
                'norutsummary' => '06',
                'status' => 'A'
            ],
            [
                'idsummary' => '0007',
                'ketsummaryrab' => 'Target Pendapatan',
                'norutsummary' => '07',
                'status' => 'A'
            ],
        ];

        DB::table('summary_rab')->delete();

        foreach ($summaryRAB as $summary) {
            DB::table('summary_rab')->insert([
                'idsummary' => $summary['idsummary'],
                'ketsummaryrab' => $summary['ketsummaryrab'],
                'norutsummary' => $summary['norutsummary'],
                'status' => $summary['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('SummaryRAB seeder completed. Inserted ' . count($summaryRAB) . ' records.');
    }
}
