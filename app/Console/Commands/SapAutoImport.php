<?php

namespace App\Console\Commands;

use App\Models\Plsap;
use App\Services\FtpService;
use App\Services\SAPImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SapAutoImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:auto-import 
                            {--force : Force import semua file meskipun sudah diimport}
                            {--directory=/ : Direktori FTP yang akan di-scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto import semua file CSV/TXT dari FTP server ke database SAP';

    protected FtpService $ftpService;
    protected SAPImportService $importService;

    public function __construct(FtpService $ftpService, SAPImportService $importService)
    {
        parent::__construct();
        $this->ftpService = $ftpService;
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        // Gunakan source directory dari FtpService sebagai default
        $directory = $this->option('directory');
        if ($directory === '/') {
            $directory = $this->ftpService->getSourceDirectory();
        }

        $this->info('========================================');
        $this->info('SAP Auto Import - ' . now()->format('Y-m-d H:i:s'));
        $this->info('========================================');
        $this->info("Directory: {$directory}");
        $this->info("Force Mode: " . ($force ? 'Ya' : 'Tidak'));
        $this->newLine();

        Log::info('SAP Auto Import started', [
            'directory' => $directory,
            'force' => $force
        ]);

        // Step 1: Test FTP connection
        $this->info('Testing FTP connection...');
        $connectionTest = $this->ftpService->testConnection();
        
        if (!$connectionTest['success']) {
            $this->error('Gagal koneksi ke FTP: ' . $connectionTest['message']);
            Log::error('SAP Auto Import - FTP connection failed', ['error' => $connectionTest['message']]);
            return Command::FAILURE;
        }
        
        $this->info('✓ FTP connected successfully');
        $this->newLine();

        // Step 2: Get list of files
        $this->info('Scanning files from FTP...');
        $filesResult = $this->ftpService->listCsvFiles($directory);

        if (!$filesResult['success']) {
            $this->error('Gagal membaca file dari FTP: ' . ($filesResult['message'] ?? 'Unknown error'));
            Log::error('SAP Auto Import - Failed to list files', ['error' => $filesResult['message'] ?? 'Unknown']);
            return Command::FAILURE;
        }

        $files = $filesResult['files'] ?? [];
        
        if (empty($files)) {
            $this->warn('Tidak ada file CSV/TXT ditemukan di FTP.');
            Log::info('SAP Auto Import - No files found');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan " . count($files) . " file.");
        $this->newLine();

        // Step 3: Process each file
        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $this->table(
            ['File', 'Size', 'Status', 'Message'],
            $this->processFiles($files, $force, $imported, $skipped, $failed)
        );

        // Summary
        $this->newLine();
        $this->info('========================================');
        $this->info('SUMMARY');
        $this->info('========================================');
        $this->info("Total files: " . count($files));
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");
        $this->info("Failed: {$failed}");

        Log::info('SAP Auto Import completed', [
            'total' => count($files),
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed
        ]);

        return Command::SUCCESS;
    }

    /**
     * Process all files and return table data
     */
    protected function processFiles(array $files, bool $force, int &$imported, int &$skipped, int &$failed): array
    {
        $tableData = [];

        foreach ($files as $file) {
            $filename = $file['name'];
            $ftpPath = $file['path'];
            $size = $file['size_formatted'];

            // Check if already imported
            $alreadyImported = Plsap::where('source_file', $filename)->exists();

            if ($alreadyImported && !$force) {
                $skipped++;
                // Tulis log untuk file yang di-skip (duplikat)
                $this->importService->writeImportLog($filename, 'SKIPPED', 'File sudah pernah diimport sebelumnya');
                $tableData[] = [$filename, $size, '⏭ Skipped', 'Sudah diimport sebelumnya'];
                continue;
            }

            // Download file
            $downloadResult = $this->ftpService->downloadToTemp($ftpPath);
            
            if (!$downloadResult['success']) {
                $failed++;
                $tableData[] = [$filename, $size, '✗ Failed', 'Download error: ' . $downloadResult['message']];
                Log::error('SAP Auto Import - Download failed', ['file' => $filename, 'error' => $downloadResult['message']]);
                continue;
            }

            $tempPath = $downloadResult['temp_path'];

            // Import file
            $importResult = $this->importService->importFromCSV($tempPath, $force, $filename);

            // Cleanup temp file
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }

            // Move file di FTP berdasarkan hasil import
            $this->moveFileAfterImport($ftpPath, $filename, $importResult);

            if ($importResult['success']) {
                $imported++;
                $recordCount = $importResult['data']['imported'] ?? 0;
                $tableData[] = [$filename, $size, '✓ Success', "{$recordCount} records → Processed/"];
                Log::info('SAP Auto Import - File imported', ['file' => $filename, 'records' => $recordCount]);
            } else {
                $failed++;
                $errorMsg = $importResult['message'] ?? 'Unknown error';
                // Truncate long error messages for table
                if (strlen($errorMsg) > 50) {
                    $errorMsg = substr($errorMsg, 0, 47) . '...';
                }
                $tableData[] = [$filename, $size, '✗ Failed', $errorMsg . ' → Error/'];
                Log::error('SAP Auto Import - Import failed', ['file' => $filename, 'error' => $importResult['message'] ?? 'Unknown']);
            }
        }

        return $tableData;
    }

    /**
     * Move file di FTP setelah import berdasarkan hasil
     * - Success → /Processed/
     * - Error/Rejected → /Error/
     */
    protected function moveFileAfterImport(string $ftpPath, string $filename, array $result): void
    {
        try {
            $destinationFolder = $result['success'] 
                ? $this->ftpService->getProcessedDirectory() 
                : $this->ftpService->getErrorDirectory();
            $destinationPath = $destinationFolder . '/' . $filename;

            $moveResult = $this->ftpService->moveFile($ftpPath, $destinationPath);

            if ($moveResult['success']) {
                $this->line("   ↳ File dipindahkan ke {$destinationFolder}/");
            } else {
                $this->warn("   ↳ Gagal memindahkan file: " . $moveResult['message']);
            }
        } catch (\Exception $e) {
            $this->warn("   ↳ Error memindahkan file: " . $e->getMessage());
            Log::error('Error moving file after import', [
                'file' => $filename,
                'error' => $e->getMessage()
            ]);
        }
    }
}
