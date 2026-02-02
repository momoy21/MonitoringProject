<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class FtpService
{
    protected string $tempFolder;
    
    public function __construct()
    {
        $this->tempFolder = storage_path('app/temp/ftp');
        $this->ensureTempFolderExists();
    }

    /**
     * Pastikan folder temporary ada
     */
    protected function ensureTempFolderExists(): void
    {
        if (!File::isDirectory($this->tempFolder)) {
            File::makeDirectory($this->tempFolder, 0755, true);
        }
    }

    /**
     * Test koneksi FTP
     */
    public function testConnection(): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Coba list direktori untuk test koneksi
            $files = $disk->files('/');
            
            return [
                'success' => true,
                'message' => 'Koneksi FTP berhasil',
                'file_count' => count($files)
            ];
        } catch (\Exception $e) {
            Log::error('FTP Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal koneksi FTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get list of CSV files from FTP
     */
    public function listCsvFiles(string $directory = '/'): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Get all files in directory
            $allFiles = $disk->files($directory);
            
            $csvFiles = [];
            foreach ($allFiles as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                // Filter hanya file CSV dan TXT
                if (in_array($extension, ['csv', 'txt'])) {
                    try {
                        $size = $disk->size($file);
                        $lastModified = $disk->lastModified($file);
                    } catch (\Exception $e) {
                        $size = 0;
                        $lastModified = null;
                    }

                    $csvFiles[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
                    ];
                }
            }

            // Sort by last modified descending
            usort($csvFiles, function ($a, $b) {
                return ($b['last_modified'] ?? '') <=> ($a['last_modified'] ?? '');
            });

            return [
                'success' => true,
                'files' => $csvFiles,
                'count' => count($csvFiles),
                'directory' => $directory
            ];
        } catch (\Exception $e) {
            Log::error('FTP List Files Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membaca daftar file FTP: ' . $e->getMessage(),
                'files' => []
            ];
        }
    }

    /**
     * Download file dari FTP ke temporary local
     */
    public function downloadToTemp(string $ftpPath): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Cek file exists
            if (!$disk->exists($ftpPath)) {
                return [
                    'success' => false,
                    'message' => "File tidak ditemukan di FTP: {$ftpPath}"
                ];
            }

            // Get file content
            $content = $disk->get($ftpPath);
            
            if ($content === false || $content === null) {
                return [
                    'success' => false,
                    'message' => 'Gagal membaca konten file dari FTP'
                ];
            }

            // Save to temp folder
            $filename = basename($ftpPath);
            $tempPath = $this->tempFolder . '/' . time() . '_' . $filename;
            
            File::put($tempPath, $content);

            Log::info('FTP Download Success', ['ftp_path' => $ftpPath, 'temp_path' => $tempPath]);

            return [
                'success' => true,
                'temp_path' => $tempPath,
                'filename' => $filename,
                'size' => strlen($content)
            ];
        } catch (\Exception $e) {
            Log::error('FTP Download Error: ' . $e->getMessage(), ['path' => $ftpPath]);
            return [
                'success' => false,
                'message' => 'Gagal download file dari FTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ensure directory exists on FTP server
     */
    public function ensureDirectoryExists(string $directory): bool
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Cek apakah direktori sudah ada
            if ($disk->directoryExists($directory)) {
                return true;
            }

            // Buat direktori jika belum ada
            $disk->makeDirectory($directory);
            Log::info('FTP Directory Created', ['directory' => $directory]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('FTP Create Directory Error: ' . $e->getMessage(), ['directory' => $directory]);
            return false;
        }
    }

    /**
     * Move file di FTP ke folder lain (misalnya Processed atau Error)
     */
    public function moveFile(string $sourcePath, string $destinationPath): array
    {
        try {
            $disk = Storage::disk('ftp');

            // Cek source exists
            if (!$disk->exists($sourcePath)) {
                return [
                    'success' => false,
                    'message' => "File sumber tidak ditemukan: {$sourcePath}"
                ];
            }

            // Pastikan folder tujuan ada
            $destinationDir = dirname($destinationPath);
            if ($destinationDir !== '.' && $destinationDir !== '/') {
                $this->ensureDirectoryExists($destinationDir);
            }

            // Move file
            $disk->move($sourcePath, $destinationPath);

            Log::info('FTP Move Success', ['from' => $sourcePath, 'to' => $destinationPath]);

            return [
                'success' => true,
                'message' => "File berhasil dipindahkan ke: {$destinationPath}"
            ];
        } catch (\Exception $e) {
            Log::error('FTP Move Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memindahkan file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete file dari FTP
     */
    public function deleteFile(string $ftpPath): array
    {
        try {
            $disk = Storage::disk('ftp');

            if (!$disk->exists($ftpPath)) {
                return [
                    'success' => false,
                    'message' => "File tidak ditemukan: {$ftpPath}"
                ];
            }

            $disk->delete($ftpPath);

            Log::info('FTP Delete Success', ['path' => $ftpPath]);

            return [
                'success' => true,
                'message' => 'File berhasil dihapus dari FTP'
            ];
        } catch (\Exception $e) {
            Log::error('FTP Delete Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cleanup temp files older than specified hours
     */
    public function cleanupTempFiles(int $hoursOld = 24): int
    {
        $count = 0;
        $cutoffTime = time() - ($hoursOld * 3600);

        $files = File::files($this->tempFolder);
        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoffTime) {
                File::delete($file);
                $count++;
            }
        }

        Log::info("FTP Temp Cleanup: Deleted {$count} old files");
        return $count;
    }

    /**
     * Get directories from FTP
     */
    public function listDirectories(string $directory = '/'): array
    {
        try {
            $disk = Storage::disk('ftp');
            $directories = $disk->directories($directory);

            return [
                'success' => true,
                'directories' => $directories
            ];
        } catch (\Exception $e) {
            Log::error('FTP List Directories Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membaca direktori FTP: ' . $e->getMessage(),
                'directories' => []
            ];
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get FTP connection info (for display, password hidden)
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => config('filesystems.disks.ftp.host'),
            'port' => config('filesystems.disks.ftp.port'),
            'username' => config('filesystems.disks.ftp.username'),
            'root' => config('filesystems.disks.ftp.root'),
            'ssl' => config('filesystems.disks.ftp.ssl'),
            'source_dir' => $this->getSourceDirectory(),
        ];
    }

    /**
     * Get source directory for CSV files
     */
    public function getSourceDirectory(): string
    {
        return env('FTP_SOURCE_DIR', '/CSV');
    }

    /**
     * Get processed directory path
     */
    public function getProcessedDirectory(): string
    {
        return '/Processed';
    }

    /**
     * Get error directory path
     */
    public function getErrorDirectory(): string
    {
        return '/Error';
    }

    /**
     * Write CSV file to FTP with given rows
     * @param string $directory Target directory (e.g., '/Processed')
     * @param string $filename Filename (e.g., 'SAPIO29012026.csv')
     * @param array $header CSV header row
     * @param array $rows Array of raw row data
     * @return array Result with success status
     */
    public function writeCsvFile(string $directory, string $filename, array $header, array $rows): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Ensure directory exists
            $this->ensureDirectoryExists($directory);

            // Build CSV content
            $csvContent = $this->buildCsvContent($header, $rows);

            // Write to FTP
            $filePath = rtrim($directory, '/') . '/' . $filename;
            $disk->put($filePath, $csvContent);

            Log::info('FTP Write CSV Success', ['path' => $filePath, 'rows' => count($rows)]);

            return [
                'success' => true,
                'path' => $filePath,
                'message' => "File berhasil ditulis: {$filePath}",
                'row_count' => count($rows)
            ];
        } catch (\Exception $e) {
            Log::error('FTP Write CSV Error: ' . $e->getMessage(), ['filename' => $filename]);
            return [
                'success' => false,
                'message' => 'Gagal menulis file CSV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Write error CSV file with Error_Reason column
     * @param string $filename Original filename (will be renamed to filename-Error.csv)
     * @param array $header Original CSV header
     * @param array $errorRows Array with 'raw_data' and 'error_reason' keys
     * @return array Result with success status
     */
    public function writeErrorCsvFile(string $filename, array $header, array $errorRows): array
    {
        try {
            $disk = Storage::disk('ftp');
            $directory = $this->getErrorDirectory();
            
            // Ensure directory exists
            $this->ensureDirectoryExists($directory);

            // Build error filename: SAPIO29012026.csv -> SAPIO29012026-Error.csv
            $pathInfo = pathinfo($filename);
            $errorFilename = $pathInfo['filename'] . '-Error.' . ($pathInfo['extension'] ?? 'csv');

            // Add Error_Reason to header
            $errorHeader = array_merge($header, ['Error_Reason']);

            // Build CSV content with error reasons
            $csvLines = [];
            $csvLines[] = $this->arrayToCsvLine($errorHeader);
            
            foreach ($errorRows as $errorRow) {
                $rowData = $errorRow['raw_data'];
                $rowData[] = $errorRow['error_reason']; // Append error reason
                $csvLines[] = $this->arrayToCsvLine($rowData);
            }

            $csvContent = implode("\n", $csvLines);

            // Write to FTP
            $filePath = rtrim($directory, '/') . '/' . $errorFilename;
            $disk->put($filePath, $csvContent);

            Log::info('FTP Write Error CSV Success', ['path' => $filePath, 'rows' => count($errorRows)]);

            return [
                'success' => true,
                'path' => $filePath,
                'filename' => $errorFilename,
                'message' => "File error ditulis: {$filePath}",
                'row_count' => count($errorRows)
            ];
        } catch (\Exception $e) {
            Log::error('FTP Write Error CSV Error: ' . $e->getMessage(), ['filename' => $filename]);
            return [
                'success' => false,
                'message' => 'Gagal menulis file error CSV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Write processed CSV file (valid rows only)
     * @param string $filename Original filename
     * @param array $header CSV header
     * @param array $validRows Array with 'raw_data' keys
     * @return array Result with success status
     */
    public function writeProcessedCsvFile(string $filename, array $header, array $validRows): array
    {
        try {
            $directory = $this->getProcessedDirectory();
            
            // Extract raw data from validRows
            $rows = array_map(fn($row) => $row['raw_data'], $validRows);
            
            return $this->writeCsvFile($directory, $filename, $header, $rows);
        } catch (\Exception $e) {
            Log::error('FTP Write Processed CSV Error: ' . $e->getMessage(), ['filename' => $filename]);
            return [
                'success' => false,
                'message' => 'Gagal menulis file processed CSV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build CSV content from header and rows
     */
    protected function buildCsvContent(array $header, array $rows): string
    {
        $lines = [];
        $lines[] = $this->arrayToCsvLine($header);
        
        foreach ($rows as $row) {
            $lines[] = $this->arrayToCsvLine($row);
        }

        return implode("\n", $lines);
    }

    /**
     * Convert array to CSV line (properly escaped)
     */
    protected function arrayToCsvLine(array $fields): string
    {
        $escaped = [];
        foreach ($fields as $field) {
            $field = (string) $field;
            // Escape if contains comma, quote, or newline
            if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                $field = '"' . str_replace('"', '""', $field) . '"';
            }
            $escaped[] = $field;
        }
        return implode(',', $escaped);
    }
}
