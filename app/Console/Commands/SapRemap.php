<?php

namespace App\Console\Commands;

use App\Services\AktualBiayaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SapRemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:remap 
                            {--force : Force re-mapping semua data (hapus existing dan mapping ulang)}
                            {--dry-run : Simulasi tanpa menyimpan ke database}
                            {--show-unmapped : Tampilkan detail cost elements yang tidak ada mapping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-mapping data dari tabel plsap ke aktual_biaya berdasarkan spec_rab_detail';

    protected AktualBiayaService $aktualBiayaService;

    public function __construct(AktualBiayaService $aktualBiayaService)
    {
        parent::__construct();
        $this->aktualBiayaService = $aktualBiayaService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $showUnmapped = $this->option('show-unmapped');

        $this->info('========================================');
        $this->info('SAP Re-Mapping - ' . now()->format('Y-m-d H:i:s'));
        $this->info('========================================');
        $this->info('Force Mode: ' . ($force ? 'Ya (hapus & mapping ulang SEMUA)' : 'Tidak'));
        $this->info('Dry Run: ' . ($dryRun ? 'Ya (simulasi saja)' : 'Tidak'));
        $this->newLine();

        // Show current state
        $this->showCurrentState();

        if ($dryRun) {
            $this->warn('⚠ DRY RUN MODE - tidak ada data yang disimpan');
            $this->newLine();
        }

        // Confirm if force mode
        if ($force && !$dryRun) {
            if (!$this->confirm('PERINGATAN: Mode force akan menghapus semua data aktual_biaya yang berasal dari plsap. Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return Command::SUCCESS;
            }

            // Delete existing mappings
            $deleted = DB::table('aktual_biaya')
                ->whereNotNull('plsap_id')
                ->delete();
            $this->info("✓ Menghapus {$deleted} data aktual_biaya dari plsap");
        }

        // Run mapping
        $this->info('Memulai proses mapping...');
        $this->newLine();

        if ($dryRun) {
            $result = $this->simulateMapping();
        } else {
            $result = $this->aktualBiayaService->processMapping(null, $force);
        }

        // Show results
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Diproses', $result['total_processed']],
                ['Berhasil Mapping', $result['total_mapped']],
                ['Dilewati (sudah ada)', $result['total_skipped']],
                ['Tidak Ada Mapping', $result['total_unmapped']],
                ['Errors', count($result['errors'] ?? [])],
            ]
        );

        // Show unmapped cost elements
        if (!empty($result['unmapped_cost_elements'])) {
            $this->newLine();
            $this->warn('Cost Elements tanpa mapping di spec_rab_detail:');
            
            $unmappedList = $result['unmapped_cost_elements'];
            
            if ($showUnmapped) {
                // Show all
                foreach ($unmappedList as $ce) {
                    $this->line("  - {$ce}");
                }
            } else {
                // Show first 5 only
                foreach (array_slice($unmappedList, 0, 5) as $ce) {
                    $this->line("  - {$ce}");
                }
                if (count($unmappedList) > 5) {
                    $this->line('  ... dan ' . (count($unmappedList) - 5) . ' lainnya (gunakan --show-unmapped untuk lihat semua)');
                }
            }

            $this->newLine();
            $this->info('Tip: Tambahkan cost elements di atas ke tabel spec_rab_detail atau jalankan seeder jika belum.');
        }

        // Show errors if any
        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error('Errors:');
            foreach (array_slice($result['errors'], 0, 10) as $error) {
                $this->line("  - {$error}");
            }
        }

        // Final state
        $this->newLine();
        $this->showCurrentState('SETELAH');

        if ($result['total_mapped'] > 0 || $force) {
            $this->newLine();
            $this->info('✓ Proses selesai!');
        }

        return Command::SUCCESS;
    }

    /**
     * Show current database state
     */
    protected function showCurrentState(string $prefix = 'SAAT INI'): void
    {
        $plsapCount = DB::table('plsap')->count();
        $aktualCount = DB::table('aktual_biaya')->count();
        $mappedCount = DB::table('aktual_biaya')->whereNotNull('plsap_id')->count();
        
        $existingPlsapIds = DB::table('aktual_biaya')
            ->whereNotNull('plsap_id')
            ->pluck('plsap_id')
            ->toArray();
        $unmappedPlsap = DB::table('plsap')
            ->whereNotIn('id', $existingPlsapIds)
            ->count();

        $this->info("=== STATUS {$prefix} ===");
        $this->table(
            ['Item', 'Jumlah'],
            [
                ['Total record di plsap', $plsapCount],
                ['Total record di aktual_biaya', $aktualCount],
                ['aktual_biaya dari plsap', $mappedCount],
                ['plsap belum ter-mapping', $unmappedPlsap],
            ]
        );
    }

    /**
     * Simulate mapping without saving
     */
    protected function simulateMapping(): array
    {
        $result = [
            'total_processed' => 0,
            'total_mapped' => 0,
            'total_skipped' => 0,
            'total_unmapped' => 0,
            'errors' => [],
            'unmapped_cost_elements' => [],
        ];

        // Build mapping cache with normalized cost_element
        $mapping = [];
        $specs = DB::table('spec_rab_detail')
            ->join('spec_rab', 'spec_rab_detail.id_spec', '=', 'spec_rab.id_spec')
            ->select('spec_rab_detail.cost_element', 'spec_rab_detail.id_spec', 'spec_rab.kategori')
            ->get();

        foreach ($specs as $spec) {
            // Normalize cost_element ke 10 digit
            $costElement = $this->normalizeCostElement($spec->cost_element);
            $mapping[$costElement] = [
                'id_spec' => $spec->id_spec,
                'kategori' => $spec->kategori ?? 'HPP',
            ];
        }

        // Get unmapped plsaps
        $existingPlsapIds = DB::table('aktual_biaya')
            ->whereNotNull('plsap_id')
            ->pluck('plsap_id')
            ->toArray();

        $plsaps = DB::table('plsap')
            ->whereNotIn('id', $existingPlsapIds)
            ->get();

        $result['total_processed'] = $plsaps->count();

        foreach ($plsaps as $plsap) {
            if (empty($plsap->cc_projek)) {
                $result['total_skipped']++;
                continue;
            }

            // Normalize cost element ke 10 digit
            $costElement = $this->normalizeCostElement($plsap->cost_element);

            if (isset($mapping[$costElement])) {
                $result['total_mapped']++;
            } else {
                $result['total_unmapped']++;
                if (!in_array($plsap->cost_element, $result['unmapped_cost_elements'])) {
                    $result['unmapped_cost_elements'][] = $plsap->cost_element;
                }
            }
        }

        return $result;
    }

    /**
     * Normalize cost element ke format 10 digit dengan leading zeros
     */
    protected function normalizeCostElement(string $costElement): string
    {
        // Jika sudah 10 digit, kembalikan langsung
        if (strlen($costElement) === 10) {
            return $costElement;
        }
        
        // Hilangkan leading zeros, lalu pad ke 10 digit
        return str_pad(ltrim($costElement, '0'), 10, '0', STR_PAD_LEFT);
    }
}
