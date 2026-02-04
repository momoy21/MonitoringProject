<?php

namespace App\Console\Commands;

use App\Services\AktualBiayaService;
use Illuminate\Console\Command;

class ProcessAktualBiaya extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aktual-biaya:process 
                            {--source= : Process hanya untuk source file tertentu}
                            {--reprocess : Proses ulang semua data termasuk yang sudah ada}
                            {--dry-run : Jalankan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses mapping data SAP ke Aktual Biaya berdasarkan Cost Element';

    protected AktualBiayaService $service;

    public function __construct(AktualBiayaService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================');
        $this->info('   PROSES MAPPING SAP KE AKTUAL BIAYA   ');
        $this->info('========================================');
        $this->newLine();

        $sourceFile = $this->option('source');
        $reprocess = $this->option('reprocess');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode DRY-RUN: Tidak ada perubahan yang akan disimpan');
            $this->newLine();
        }

        if ($sourceFile) {
            $this->info("Processing source file: {$sourceFile}");
            $result = $this->service->processBySourceFile($sourceFile);
        } else {
            if ($reprocess) {
                $this->warn('Mode REPROCESS: Memproses ulang semua data');
            }
            $result = $this->service->processMapping(null, $reprocess);
        }

        $this->newLine();
        $this->info('========================================');
        $this->info('              HASIL PROSES              ');
        $this->info('========================================');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Diproses', $result['total_processed']],
                ['Berhasil Mapping', $result['total_mapped']],
                ['Dilewati (Sudah Ada/Kosong)', $result['total_skipped']],
                ['Tidak Ada Mapping', $result['total_unmapped']],
                ['Error', count($result['errors'])],
            ]
        );

        if (!empty($result['unmapped_cost_elements'])) {
            $this->newLine();
            $this->warn('Cost Elements tanpa mapping:');
            foreach ($result['unmapped_cost_elements'] as $ce) {
                $this->line("  - {$ce}");
            }
            $this->newLine();
            $this->info('Tambahkan mapping di menu Spesifikasi RAB Detail untuk Cost Elements di atas.');
        }

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->error('Errors:');
            foreach (array_slice($result['errors'], 0, 10) as $error) {
                $this->line("  - {$error}");
            }
            if (count($result['errors']) > 10) {
                $this->line("  ... dan " . (count($result['errors']) - 10) . " error lainnya");
            }
        }

        $this->newLine();

        if ($result['success']) {
            $this->info('✓ Proses selesai dengan sukses');
            return Command::SUCCESS;
        } else {
            $this->error('✗ Proses selesai dengan error');
            return Command::FAILURE;
        }
    }
}
