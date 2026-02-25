<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AktualBiayaSeeder extends Seeder
{
    public function run(): void
    {
        // Use real cost centers from history_proyek
        $ccProjects = DB::table('history_proyek')
            ->whereIn('status', ['O', 'I'])
            ->pluck('cost_center')
            ->unique()
            ->values()
            ->toArray();

        if (empty($ccProjects)) {
            $this->command->warn('No history_proyek records with status O/I found. Skipping.');
            return;
        }

        // Use real spec IDs
        $idSpecs = DB::table('spec_rab')->pluck('id_spec')->toArray();
        if (empty($idSpecs)) {
            $idSpecs = ['0001', '0002', '0003'];
        }

        $kategoris = ['PDP', 'HPP'];
        $data = [];
        $counter = 1;

        // Generate 3-5 aktual_biaya records per cost center
        foreach ($ccProjects as $cc) {
            $numRecords = rand(3, 5);
            for ($j = 0; $j < $numRecords; $j++) {
                $idSpec = $idSpecs[array_rand($idSpecs)];
                $kategori = $kategoris[array_rand($kategoris)];
                $tanggalPosting = now()->subDays(rand(1, 180));
                $bulan = $tanggalPosting->copy()->startOfMonth();
                $idAktual = date('ymd') . str_pad($counter, 4, '0', STR_PAD_LEFT);

                $data[] = [
                    'cc_projek'       => $cc,
                    'id_aktual'       => $idAktual,
                    'id_spec'         => $idSpec,
                    'tanggal_posting' => $tanggalPosting->format('Y-m-d'),
                    'bulan'           => $bulan->format('Y-m-d'),
                    'nilai'           => rand(5000000, 300000000),
                    'kategori'        => $kategori,
                    'plsap_id'        => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
                $counter++;
            }
        }

        // Truncate existing data and insert
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('aktual_biaya')->truncate();
        foreach (array_chunk($data, 50) as $chunk) {
            DB::table('aktual_biaya')->insert($chunk);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Seeded ' . count($data) . ' aktual_biaya records for ' . count($ccProjects) . ' cost centers.');
    }
}
