<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KuotaLembur;
use App\Models\Karyawan;
use App\Models\DataProyek;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KuotaLemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing active karyawan NIKs
        $niks = Karyawan::where('aktif', 'Y')->pluck('nik')->take(15)->toArray();
        
        if (empty($niks)) {
            $this->command->error('No active karyawan found. Please seed karyawan data first.');
            return;
        }

        $this->command->info('Found ' . count($niks) . ' active karyawan.');

        // Get cost_center from existing projects
        $costCenters = DataProyek::whereNotNull('cost_center')
            ->where('cost_center', '!=', '')
            ->pluck('cost_center')
            ->unique()
            ->take(10)
            ->values()
            ->toArray();
        
        if (empty($costCenters)) {
            $costCenters = ['CC001', 'CC002', 'CC003'];
            $this->command->warn('No existing projects found. Using default cost centers.');
        } else {
            $this->command->info('Found ' . count($costCenters) . ' cost centers.');
        }

        // Clear existing kuota lembur data
        DB::table('kuota_lembur')->truncate();
        $this->command->info('Cleared existing kuota lembur data.');

        // Sample data for kuota lembur
        $kuotaData = [];
        
        // Generate data for multiple periods
        $periods = [
            ['awal' => '2025-11-01', 'akhir' => '2025-11-30'],
            ['awal' => '2025-12-01', 'akhir' => '2025-12-31'],
            ['awal' => '2026-01-01', 'akhir' => '2026-01-31'],
            ['awal' => '2026-02-01', 'akhir' => '2026-02-28'],
        ];

        $bulanCounter = [];
        $insertCount = 0;
        
        foreach ($periods as $period) {
            foreach ($niks as $index => $nik) {
                $costCenter = $costCenters[array_rand($costCenters)];
                $dokIo = 'IO' . str_pad(rand(1, 999), 6, '0', STR_PAD_LEFT);
                
                // Track bulan per NIK
                if (!isset($bulanCounter[$nik])) {
                    $bulanCounter[$nik] = 1;
                } else {
                    $bulanCounter[$nik]++;
                }

                DB::table('kuota_lembur')->insert([
                    'cost_center' => $costCenter,
                    'dok_io' => $dokIo,
                    'nik' => $nik,
                    'bulan' => $bulanCounter[$nik],
                    'periode_awal' => $period['awal'],
                    'periode_akhir' => $period['akhir'],
                    'jml_wd' => rand(4, 20),
                    'jml_we' => rand(2, 10),
                    'jml_hn' => rand(0, 4),
                    'status' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertCount++;
            }
        }

        $this->command->info("Kuota Lembur seeded: {$insertCount} records");
        
        // Display summary
        $totalPending = DB::table('kuota_lembur')->whereNull('status')->count();
        $this->command->info("Total pending records: {$totalPending}");
    }
}
