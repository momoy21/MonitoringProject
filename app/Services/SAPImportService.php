<?php

namespace App\Services;

use App\Models\Plsap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SAPImportService
{
    protected string $errorFolder;
    protected string $logFolder;
    protected string $processedFolder;

    // Konfigurasi field wajib - bisa disesuaikan
    protected array $requiredFields = [
        'InternalOrder',
        'CCProjek',
        'CostElement',
        'DescriptionCE',
        'AmountLocal',
        'PostingDate',
    ];

    // Field yang boleh kosong
    protected array $optionalFields = [
        'DescriptionIO',
        'ProfitCenter',
        'DescriptioPCA',
    ];

    public function __construct()
    {
        $this->errorFolder = storage_path('app/sap/Error');
        $this->logFolder = storage_path('app/sap/LOG');
        $this->processedFolder = storage_path('app/sap/Processed');

        $this->ensureFoldersExist();
    }

    /**
     * Pastikan folder-folder yang diperlukan ada
     */
    protected function ensureFoldersExist(): void
    {
        $folders = [$this->errorFolder, $this->logFolder, $this->processedFolder];

        foreach ($folders as $folder) {
            if (!File::isDirectory($folder)) {
                File::makeDirectory($folder, 0755, true);
            }
        }
    }

    /**
     * Log error ke file harian - format: LOG/error_log_yyyymmdd.txt
     */
    protected function logError(string $sourceFile, string $type, string $message, ?int $rowNumber = null): void
    {
        $logFile = $this->logFolder . '/error_log_' . date('Ymd') . '.txt';
        $timestamp = date('Y-m-d H:i:s');
        
        $rowInfo = $rowNumber ? " [Baris {$rowNumber}]" : "";
        $logEntry = "[{$timestamp}] [{$sourceFile}]{$rowInfo} {$type}: {$message}" . PHP_EOL;

        File::append($logFile, $logEntry);
        Log::error("SAP Import - {$type}: {$message}", ['file' => $sourceFile, 'row' => $rowNumber]);
    }

    /**
     * Log import activity ke file harian
     */
    protected function logImport(string $sourceFile, string $status, string $message): void
    {
        $logFile = $this->logFolder . '/import_log_' . date('Ymd') . '.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$status}] [{$sourceFile}] {$message}" . PHP_EOL;

        File::append($logFile, $logEntry);
    }

    /**
     * Pindahkan file corrupt ke folder Error
     */
    protected function moveToErrorFolder(string $filePath): bool
    {
        try {
            $filename = basename($filePath);
            $destination = $this->errorFolder . '/' . date('Ymd_His') . '_' . $filename;

            if (File::exists($filePath)) {
                File::copy($filePath, $destination);
                $this->logImport($filename, 'MOVED_TO_ERROR', "File dipindahkan ke: {$destination}");
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to move file to error folder", ['file' => $filePath, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Hitung hash file untuk deteksi duplikat
     */
    protected function getFileHash(string $filePath): string
    {
        return md5_file($filePath);
    }

    /**
     * Cek apakah file sudah pernah diimport (duplikat)
     */
    protected function isDuplicateFile(string $filePath, bool $checkByContent = true): array
    {
        $filename = basename($filePath);

        // 1. Cek berdasarkan nama file di tabel plsap
        $existingByName = Plsap::where('source_file', $filename)->first();
        if ($existingByName) {
            return [
                'is_duplicate' => true,
                'reason' => 'filename',
                'message' => "File '{$filename}' sudah pernah diimport pada " .
                    Carbon::parse($existingByName->imported_at)->format('d/m/Y H:i:s') .
                    ". Total " . Plsap::where('source_file', $filename)->count() . " record."
            ];
        }

        // 2. Cek berdasarkan hash konten file di tabel sap_import_history
        if ($checkByContent) {
            $fileHash = $this->getFileHash($filePath);

            try {
                $existingByHash = DB::table('sap_import_history')
                    ->where('file_hash', $fileHash)
                    ->first();

                if ($existingByHash) {
                    return [
                        'is_duplicate' => true,
                        'reason' => 'content',
                        'message' => "File dengan konten yang sama sudah pernah diimport sebagai '{$existingByHash->filename}' pada " .
                            Carbon::parse($existingByHash->imported_at)->format('d/m/Y H:i:s')
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("sap_import_history table check failed: " . $e->getMessage());
            }
        }

        return ['is_duplicate' => false];
    }

    /**
     * Simpan history import untuk tracking
     */
    protected function recordImportHistory(string $filePath, string $status, int $recordCount = 0): void
    {
        try {
            DB::table('sap_import_history')->insert([
                'filename' => basename($filePath),
                'file_hash' => $this->getFileHash($filePath),
                'file_size' => File::size($filePath),
                'record_count' => $recordCount,
                'status' => $status,
                'imported_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to record import history: " . $e->getMessage());
        }
    }

    /**
     * Validasi struktur file CSV
     */
    protected function validateFileStructure(string $filePath): array
    {
        $filename = basename($filePath);

        if (!File::exists($filePath)) {
            return [
                'valid' => false,
                'error' => 'FILE_NOT_FOUND',
                'message' => "File tidak ditemukan: {$filePath}"
            ];
        }

        if (!is_readable($filePath)) {
            return [
                'valid' => false,
                'error' => 'FILE_NOT_READABLE',
                'message' => "File tidak dapat dibaca: {$filePath}"
            ];
        }

        if (File::size($filePath) === 0) {
            return [
                'valid' => false,
                'error' => 'FILE_EMPTY',
                'message' => "File kosong: {$filePath}"
            ];
        }

        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'valid' => false,
                'error' => 'FILE_CORRUPT',
                'message' => "File corrupt atau tidak dapat dibuka: {$filePath}"
            ];
        }

        $header = fgetcsv($handle);
        fclose($handle);

        if ($header === false || empty($header)) {
            return [
                'valid' => false,
                'error' => 'INVALID_CSV_FORMAT',
                'message' => "Format CSV tidak valid atau file corrupt"
            ];
        }

        return ['valid' => true, 'header' => $header];
    }

    /**
     * Validasi satu baris data - return array of errors
     */
    protected function validateRowData(array $row, array $columnMap, int $rowNumber): array
    {
        $errors = [];
        $warnings = [];

        // Validasi field wajib
        foreach ($this->requiredFields as $field) {
            if (!isset($columnMap[$field])) {
                $errors[] = "Kolom '{$field}' tidak ditemukan di file";
                continue;
            }

            $value = trim($row[$columnMap[$field]] ?? '');
            
            if (empty($value)) {
                $errors[] = "{$field} kosong/tidak lengkap";
            }
        }

        // Validasi format AmountLocal (harus angka)
        if (isset($columnMap['AmountLocal'])) {
            $amount = trim($row[$columnMap['AmountLocal']] ?? '');
            if (!empty($amount)) {
                $cleanedAmount = preg_replace('/[^\d\-\.,]/', '', $amount);
                if (empty($cleanedAmount) || !is_numeric(str_replace(['.', ','], ['', '.'], $cleanedAmount))) {
                    $errors[] = "AmountLocal bukan format angka valid: '{$amount}'";
                }
            }
        }

        // Validasi format PostingDate
        if (isset($columnMap['PostingDate'])) {
            $postingDate = trim($row[$columnMap['PostingDate']] ?? '');
            if (!empty($postingDate)) {
                $parsedDate = $this->parseDate($postingDate);
                if ($parsedDate === null) {
                    $errors[] = "PostingDate format tidak valid: '{$postingDate}'";
                }
            }
        }

        // Warning untuk optional fields yang kosong (tidak menyebabkan error)
        foreach ($this->optionalFields as $field) {
            if (isset($columnMap[$field])) {
                $value = trim($row[$columnMap[$field]] ?? '');
                if (empty($value)) {
                    $warnings[] = "{$field} kosong";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Import data dari file CSV SAP
     * Mode: ALL OR NOTHING - Jika ada satu baris error, seluruh file ditolak
     */
    public function importFromCSV(string $filePath, bool $force = false): array
    {
        $sourceFile = basename($filePath);

        $this->logImport($sourceFile, 'STARTED', "Memulai proses import" . ($force ? " (FORCE MODE)" : ""));

        try {
            // ================================================================
            // STEP 1: Validasi struktur file
            // ================================================================
            $validation = $this->validateFileStructure($filePath);
            if (!$validation['valid']) {
                $this->logError($sourceFile, $validation['error'], $validation['message']);
                $this->logImport($sourceFile, 'FAILED', $validation['message']);

                if (in_array($validation['error'], ['FILE_CORRUPT', 'INVALID_CSV_FORMAT', 'FILE_EMPTY'])) {
                    $this->moveToErrorFolder($filePath);
                }

                return [
                    'success' => false,
                    'error_type' => $validation['error'],
                    'message' => $validation['message']
                ];
            }

            // ================================================================
            // STEP 2: Cek duplikat (skip jika force = true)
            // ================================================================
            if (!$force) {
                $duplicateCheck = $this->isDuplicateFile($filePath);
                if ($duplicateCheck['is_duplicate']) {
                    $this->logImport($sourceFile, 'DUPLICATE', $duplicateCheck['message']);

                    return [
                        'success' => false,
                        'error_type' => 'DUPLICATE_FILE',
                        'message' => $duplicateCheck['message'],
                        'is_duplicate' => true,
                        'duplicate_reason' => $duplicateCheck['reason']
                    ];
                }
            } else {
                // Force mode: Hapus data lama
                $deletedCount = Plsap::where('source_file', $sourceFile)->count();
                if ($deletedCount > 0) {
                    Plsap::where('source_file', $sourceFile)->delete();
                    $this->logImport($sourceFile, 'FORCE_DELETE', "Menghapus {$deletedCount} record lama");
                }

                try {
                    DB::table('sap_import_history')->where('filename', $sourceFile)->delete();
                } catch (\Exception $e) {}
            }

            // ================================================================
            // STEP 3: VALIDASI SELURUH DATA TERLEBIH DAHULU (PRE-VALIDATION)
            // ================================================================
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception("Gagal membuka file: {$filePath}");
            }

            // Baca header
            $header = fgetcsv($handle);
            $header = array_map('trim', $header);

            Log::info("SAP Import - Headers: " . implode(', ', $header));

            // Mapping kolom
            $expectedHeaders = [
                'InternalOrder', 'CCProjek', 'DescriptionIO', 'CostElement',
                'DescriptionCE', 'AmountLocal', 'PostingDate', 'ProfitCenter', 'DescriptioPCA'
            ];

            $columnMap = [];
            foreach ($expectedHeaders as $expected) {
                foreach ($header as $index => $col) {
                    if (strcasecmp(trim($col), $expected) === 0) {
                        $columnMap[$expected] = $index;
                        break;
                    }
                }
                // Alternative names
                if (!isset($columnMap[$expected])) {
                    $alternatives = [
                        'DescriptioPCA' => ['DescriptionPCA', 'Description_PCA'],
                        'CCProjek' => ['CC_Projek', 'CCProject'],
                    ];
                    if (isset($alternatives[$expected])) {
                        foreach ($alternatives[$expected] as $alt) {
                            foreach ($header as $index => $col) {
                                if (strcasecmp(trim($col), $alt) === 0) {
                                    $columnMap[$expected] = $index;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            // Cek missing columns
            $missingColumns = array_diff($expectedHeaders, array_keys($columnMap));
            if (!empty($missingColumns)) {
                fclose($handle);
                $errorMsg = 'Kolom yang hilang: ' . implode(', ', $missingColumns);
                $this->logError($sourceFile, 'HEADER_VALIDATION_FAILED', $errorMsg);
                $this->moveToErrorFolder($filePath);

                return [
                    'success' => false,
                    'error_type' => 'MISSING_COLUMNS',
                    'message' => $errorMsg,
                    'found_headers' => $header
                ];
            }

            // ================================================================
            // PRE-VALIDATION: Baca dan validasi SEMUA baris terlebih dahulu
            // ================================================================
            $allRows = [];
            $errorRows = [];
            $warningRows = [];
            $rowCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowCount++;
                $currentRowNum = $rowCount + 1; // +1 untuk header

                // Skip baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validasi baris
                $rowValidation = $this->validateRowData($row, $columnMap, $currentRowNum);

                // Kumpulkan warnings
                if (!empty($rowValidation['warnings'])) {
                    $warningRows[] = [
                        'row' => $currentRowNum,
                        'warnings' => $rowValidation['warnings'],
                        'data' => $this->getRowPreview($row, $columnMap)
                    ];
                }

                // Jika ada error, kumpulkan untuk ditampilkan
                if (!$rowValidation['valid']) {
                    $errorRows[] = [
                        'row' => $currentRowNum,
                        'errors' => $rowValidation['errors'],
                        'data' => $this->getRowPreview($row, $columnMap)
                    ];

                    // Log setiap error per baris
                    $errorMsg = implode('; ', $rowValidation['errors']);
                    $this->logError($sourceFile, 'DATA_INCOMPLETE', $errorMsg, $currentRowNum);
                } else {
                    // Simpan baris yang valid untuk diproses nanti
                    $allRows[] = [
                        'row_number' => $currentRowNum,
                        'data' => $row
                    ];
                }
            }

            fclose($handle);

            // ================================================================
            // JIKA ADA ERROR, TOLAK SELURUH FILE (ALL OR NOTHING)
            // ================================================================
            if (!empty($errorRows)) {
                $totalErrors = count($errorRows);
                $errorSummary = "File ditolak! Ditemukan {$totalErrors} baris dengan data tidak lengkap/tidak valid.";
                
                // Log semua error summary
                $this->logImport($sourceFile, 'REJECTED', $errorSummary);
                
                // Pindahkan file ke folder Error
                $this->moveToErrorFolder($filePath);

                // Build detail message
                $detailMessage = $errorSummary . " Baris bermasalah: ";
                $rowNumbers = array_column($errorRows, 'row');
                if (count($rowNumbers) <= 10) {
                    $detailMessage .= implode(', ', $rowNumbers);
                } else {
                    $detailMessage .= implode(', ', array_slice($rowNumbers, 0, 10)) . "... dan " . (count($rowNumbers) - 10) . " baris lainnya";
                }
                $detailMessage .= ". Silakan perbaiki file dan upload ulang.";

                return [
                    'success' => false,
                    'error_type' => 'DATA_VALIDATION_FAILED',
                    'message' => $detailMessage,
                    'data' => [
                        'total_rows' => $rowCount,
                        'valid_rows' => count($allRows),
                        'error_rows' => $totalErrors,
                        'warning_rows' => count($warningRows)
                    ],
                    'errors' => $errorRows,
                    'warnings' => $warningRows
                ];
            }

            // ================================================================
            // STEP 4: SEMUA DATA VALID - LAKUKAN INSERT
            // ================================================================
            if (empty($allRows)) {
                $message = "File tidak memiliki data yang bisa diimport.";
                $this->logImport($sourceFile, 'FAILED', $message);

                return [
                    'success' => false,
                    'error_type' => 'NO_DATA',
                    'message' => $message
                ];
            }

            DB::beginTransaction();

            try {
                $importedCount = 0;

                foreach ($allRows as $rowData) {
                    $row = $rowData['data'];

                    // Parse data
                    $postingDateRaw = trim($row[$columnMap['PostingDate']] ?? '');
                    $postingDate = $this->parseDate($postingDateRaw);

                    $amountRaw = trim($row[$columnMap['AmountLocal']] ?? '0');
                    $amount = $this->parseAmount($amountRaw);

                    // Simpan ke database
                    Plsap::create([
                        'internal_order' => trim($row[$columnMap['InternalOrder']] ?? ''),
                        'cc_projek' => trim($row[$columnMap['CCProjek']] ?? ''),
                        'description_io' => trim($row[$columnMap['DescriptionIO']] ?? ''),
                        'cost_element' => trim($row[$columnMap['CostElement']] ?? ''),
                        'description_ce' => trim($row[$columnMap['DescriptionCE']] ?? ''),
                        'amount_local' => $amount,
                        'posting_date' => $postingDate,
                        'profit_center' => trim($row[$columnMap['ProfitCenter']] ?? ''),
                        'description_pca' => trim($row[$columnMap['DescriptioPCA']] ?? ''),
                        'source_file' => $sourceFile,
                        'imported_at' => now(),
                    ]);

                    $importedCount++;
                }

                DB::commit();

                // Record import history
                $this->recordImportHistory($filePath, 'SUCCESS', $importedCount);

                // Build success message
                $message = "Import berhasil! {$importedCount} record diimport dari total {$rowCount} baris.";
                if (!empty($warningRows)) {
                    $message .= " (" . count($warningRows) . " baris dengan warning pada field opsional)";
                }

                $this->logImport($sourceFile, 'SUCCESS', $message);

                Log::info("SAP Import completed", [
                    'file' => $sourceFile,
                    'imported' => $importedCount,
                    'warnings' => count($warningRows)
                ]);

                return [
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'total_rows' => $rowCount,
                        'imported' => $importedCount,
                        'skipped' => 0,
                        'error_count' => 0,
                        'warning_count' => count($warningRows)
                    ],
                    'warnings' => $warningRows
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }

            $errorMessage = 'Import gagal: ' . $e->getMessage();
            $this->logError($sourceFile, 'IMPORT_EXCEPTION', $e->getMessage());
            $this->logImport($sourceFile, 'FAILED', $errorMessage);
            $this->moveToErrorFolder($filePath);

            return [
                'success' => false,
                'error_type' => 'EXCEPTION',
                'message' => $errorMessage
            ];
        }
    }

    /**
     * Get preview data dari row untuk debugging
     */
    private function getRowPreview(array $row, array $columnMap): array
    {
        return [
            'InternalOrder' => trim($row[$columnMap['InternalOrder'] ?? 0] ?? ''),
            'CCProjek' => trim($row[$columnMap['CCProjek'] ?? 1] ?? ''),
            'CostElement' => trim($row[$columnMap['CostElement'] ?? 3] ?? ''),
            'DescriptionCE' => trim($row[$columnMap['DescriptionCE'] ?? 4] ?? ''),
            'AmountLocal' => trim($row[$columnMap['AmountLocal'] ?? 5] ?? ''),
        ];
    }

    /**
     * Force import - hapus data lama dan import ulang
     */
    public function forceImportFromCSV(string $filePath): array
    {
        return $this->importFromCSV($filePath, true);
    }

    /**
     * Get error logs untuk tanggal tertentu
     */
    public function getErrorLogs(string $date = null): string
    {
        $date = $date ?? date('Ymd');
        $logFile = $this->logFolder . '/error_log_' . $date . '.txt';

        if (File::exists($logFile)) {
            return File::get($logFile);
        }

        return "Tidak ada error log untuk tanggal {$date}";
    }

    /**
     * Get import logs untuk tanggal tertentu
     */
    public function getImportLogs(string $date = null): string
    {
        $date = $date ?? date('Ymd');
        $logFile = $this->logFolder . '/import_log_' . $date . '.txt';

        if (File::exists($logFile)) {
            return File::get($logFile);
        }

        return "Tidak ada import log untuk tanggal {$date}";
    }

    /**
     * Parse date dari berbagai format
     */
    private function parseDate(string $value): ?string
    {
        if (empty($value)) return null;

        // Format YYYYMMDD
        if (strlen($value) === 8 && is_numeric($value)) {
            try {
                return Carbon::createFromFormat('Ymd', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Format dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Format yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse amount dari string ke decimal
     */
    private function parseAmount(string $value): float
    {
        if (empty($value)) return 0;

        $cleaned = preg_replace('/[^\d\-\.,]/', '', $value);

        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (strpos($cleaned, ',') !== false) {
            $lastComma = strrpos($cleaned, ',');
            $afterComma = substr($cleaned, $lastComma + 1);
            if (strlen($afterComma) <= 2) {
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
        }

        return (float) $cleaned;
    }
}