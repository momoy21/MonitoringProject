<?php

namespace App\Console\Commands;

use App\Models\Plsap;
use App\Services\FtpService;
use App\Services\SAPImportService;
use App\Services\AktualBiayaService;
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
                            {--directory=/ : Direktori FTP yang akan di-scan}
                            {--skip-mapping : Skip proses mapping ke Aktual Biaya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto import semua file CSV/TXT dari FTP server ke database SAP dan mapping ke Aktual Biaya';

    protected FtpService $ftpService;
    protected SAPImportService $importService;
    protected AktualBiayaService $aktualBiayaService;

    public function __construct(FtpService $ftpService, SAPImportService $importService, AktualBiayaService $aktualBiayaService)
    {
        parent::__construct();
        $this->ftpService = $ftpService;
        $this->importService = $importService;
        $this->aktualBiayaService = $aktualBiayaService;
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

        // Step 4: Proses mapping ke Aktual Biaya (jika ada yang berhasil diimport)
        if ($imported > 0 && !$this->option('skip-mapping')) {
            $this->newLine();
            $this->info('========================================');
            $this->info('MAPPING KE AKTUAL BIAYA');
            $this->info('========================================');
            
            $mappingResult = $this->aktualBiayaService->processMapping();
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Diproses', $mappingResult['total_processed']],
                    ['Berhasil Mapping', $mappingResult['total_mapped']],
                    ['Dilewati', $mappingResult['total_skipped']],
                    ['Tidak Ada Mapping', $mappingResult['total_unmapped']],
                ]
            );

            if (!empty($mappingResult['unmapped_cost_elements'])) {
                $this->warn('Cost Elements tanpa mapping: ' . implode(', ', array_slice($mappingResult['unmapped_cost_elements'], 0, 5)));
                if (count($mappingResult['unmapped_cost_elements']) > 5) {
                    $this->warn('... dan ' . (count($mappingResult['unmapped_cost_elements']) - 5) . ' lainnya');
                }
            }

            Log::info('SAP Auto Import - Aktual Biaya mapping completed', [
                'mapped' => $mappingResult['total_mapped'],
                'unmapped' => $mappingResult['total_unmapped'],
            ]);
        }

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
     * - Valid rows → /Processed/filename.csv
     * - Invalid rows → /Error/filename-Error.csv (dengan Error_Reason column)
     * - Original file dihapus dari source
     */
    protected function moveFileAfterImport(string $ftpPath, string $filename, array $result): void
    {
        try {
            // Ambil data raw rows dari result
            $header = $result['header'] ?? [];
            $validRawRows = $result['valid_raw_rows'] ?? [];
            $errorRawRows = $result['error_raw_rows'] ?? [];

            $validCount = count($validRawRows);
            $errorCount = count($errorRawRows);

            $this->line("   ↳ Validasi: {$validCount} valid, {$errorCount} error");

            // Jika ada header dan ada data untuk diproses
            if (!empty($header)) {
                // Tulis valid rows ke /Processed/filename.csv jika ada
                if ($validCount > 0) {
                    $processedResult = $this->ftpService->writeProcessedCsvFile($filename, $header, $validRawRows);
                    if ($processedResult['success']) {
                        $this->info("   ↳ ✓ {$validCount} valid rows → /Processed/{$filename}");
                        Log::info("Split file - Valid rows written", [
                            'file' => $filename,
                            'path' => $processedResult['path'],
                            'rows' => $validCount
                        ]);
                    } else {
                        $this->warn("   ↳ ✗ Gagal menulis processed file: " . $processedResult['message']);
                    }
                }

                // Tulis error rows ke /Error/filename-Error.csv jika ada
                if ($errorCount > 0) {
                    $errorResult = $this->ftpService->writeErrorCsvFile($filename, $header, $errorRawRows);
                    if ($errorResult['success']) {
                        $this->warn("   ↳ ⚠ {$errorCount} error rows → /Error/{$errorResult['filename']}");
                        Log::info("Split file - Error rows written", [
                            'file' => $filename,
                            'error_file' => $errorResult['filename'],
                            'path' => $errorResult['path'],
                            'rows' => $errorCount
                        ]);
                    } else {
                        $this->warn("   ↳ ✗ Gagal menulis error file: " . $errorResult['message']);
                    }
                }
            } else {
                // Fallback: jika tidak ada data raw rows (untuk backward compatibility)
                // Pindahkan file utuh ke folder sesuai status
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
                return; // Exit early for fallback case
            }

            // Hapus file original dari source
            $deleteResult = $this->ftpService->deleteFile($ftpPath);
            if ($deleteResult['success']) {
                $this->line("   ↳ 🗑 File original dihapus dari source");
            } else {
                $this->warn("   ↳ Gagal menghapus file original: " . $deleteResult['message']);
            }

        } catch (\Exception $e) {
            $this->warn("   ↳ Error memproses file split: " . $e->getMessage());
            Log::error('Error processing split file after import', [
                'file' => $filename,
                'error' => $e->getMessage()
            ]);
        }
    }
}
