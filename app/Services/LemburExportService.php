<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LemburExportService
{
    protected string $tempFolder;
    protected string $ftpTargetDir;

    public function __construct()
    {
        $this->tempFolder = storage_path('app/temp/lembur');
        $this->ftpTargetDir = env('FTP_LEMBUR_TARGET_DIR', '/EMS/Lembur');
        $this->ensureTempFolderExists();
    }

    /**
     * Ensure temp folder exists
     */
    protected function ensureTempFolderExists(): void
    {
        if (!File::isDirectory($this->tempFolder)) {
            File::makeDirectory($this->tempFolder, 0755, true);
        }
    }

    /**
     * Test FTP connection with detailed diagnostics
     */
    public function testFtpConnection(): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            // Try to list root directory to test connection
            $rootFiles = $disk->files('/');
            $rootDirs = $disk->directories('/');
            
            // Check if target directory exists
            $targetExists = false;
            $canCreateDir = false;
            
            try {
                $targetExists = $disk->exists($this->ftpTargetDir);
                
                // If target doesn't exist, try to create it
                if (!$targetExists) {
                    $this->ensureFtpDirectoryExists($disk, $this->ftpTargetDir);
                    $targetExists = $disk->exists($this->ftpTargetDir);
                    $canCreateDir = $targetExists;
                }
            } catch (\Exception $e) {
                Log::warning('Could not check/create target directory: ' . $e->getMessage());
            }
            
            return [
                'success' => true,
                'message' => 'Koneksi FTP berhasil',
                'ftp_host' => env('FTP_HOST'),
                'ftp_root' => env('FTP_ROOT', '/'),
                'target_directory' => $this->ftpTargetDir,
                'target_exists' => $targetExists,
                'directory_created' => $canCreateDir,
                'root_files_count' => count($rootFiles),
                'root_dirs' => $rootDirs,
            ];
        } catch (\Exception $e) {
            Log::error('Lembur FTP Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal koneksi FTP: ' . $e->getMessage(),
                'ftp_host' => env('FTP_HOST'),
                'target_directory' => $this->ftpTargetDir,
            ];
        }
    }

    /**
     * Export data to CSV and upload to FTP
     */
    public function exportToFtp(Collection $data, string $periodeAwal, string $periodeAkhir): array
    {
        try {
            // Generate filename: LEMBUR_<PeriodeAwal>_<PeriodeAkhir>.csv
            $periodeAwalFormatted = Carbon::parse($periodeAwal)->format('Ymd');
            $periodeAkhirFormatted = Carbon::parse($periodeAkhir)->format('Ymd');
            $filename = "LEMBUR_{$periodeAwalFormatted}_{$periodeAkhirFormatted}.csv";

            // Generate CSV content
            $csvContent = $this->generateCsvContent($data);

            // Save to temp file first (for backup/debugging)
            $tempPath = $this->tempFolder . '/' . $filename;
            File::put($tempPath, $csvContent);
            
            Log::info('Lembur CSV created locally', ['path' => $tempPath, 'records' => $data->count()]);

            // Upload to FTP
            $uploadResult = $this->uploadToFtp($filename, $csvContent);

            if (!$uploadResult['success']) {
                return $uploadResult;
            }

            return [
                'success' => true,
                'message' => "Data berhasil diekspor dan diunggah ke FTP. File: {$filename}",
                'filename' => $filename,
                'path' => $uploadResult['path'],
                'total_records' => $data->count(),
            ];

        } catch (\Exception $e) {
            Log::error('Lembur Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal membuat file CSV: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate CSV content from data
     */
    protected function generateCsvContent(Collection $data): string
    {
        $lines = [];

        // Header row
        $header = ['NIK', 'CostCentre', 'periodeawal', 'periodeakhir', 'JmlWD', 'JmlWE', 'JmlHN'];
        $lines[] = implode(',', $header);

        // Data rows
        foreach ($data as $item) {
            $row = [
                $this->escapeForCsv($item->nik),
                $this->escapeForCsv($item->cost_center),
                $item->periode_awal->format('Y-m-d'),
                $item->periode_akhir->format('Y-m-d'),
                $item->jml_wd,
                $item->jml_we,
                $item->jml_hn,
            ];
            $lines[] = implode(',', $row);
        }

        return implode("\n", $lines);
    }

    /**
     * Escape value for CSV
     */
    protected function escapeForCsv($value): string
    {
        $value = (string) $value;
        
        // If value contains comma, newline, or quote, wrap in quotes
        if (preg_match('/[,"\n\r]/', $value)) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        
        return $value;
    }

    /**
     * Upload CSV file to FTP
     */
    protected function uploadToFtp(string $filename, string $content): array
    {
        try {
            $disk = Storage::disk('ftp');

            // Ensure target directory exists
            $this->ensureFtpDirectoryExists($disk, $this->ftpTargetDir);

            // Full path
            $ftpPath = rtrim($this->ftpTargetDir, '/') . '/' . $filename;

            // Upload file
            $result = $disk->put($ftpPath, $content);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengunggah file ke FTP',
                ];
            }

            Log::info('Lembur CSV uploaded to FTP', ['path' => $ftpPath]);

            return [
                'success' => true,
                'message' => 'File berhasil diunggah ke FTP',
                'path' => $ftpPath,
            ];

        } catch (\Exception $e) {
            Log::error('Lembur FTP Upload Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengunggah ke FTP: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ensure FTP directory exists, create if not
     */
    protected function ensureFtpDirectoryExists($disk, string $directory): void
    {
        try {
            // Split path and create each level
            $parts = array_filter(explode('/', $directory));
            $currentPath = '';

            foreach ($parts as $part) {
                $currentPath .= '/' . $part;
                
                if (!$disk->exists($currentPath)) {
                    $disk->makeDirectory($currentPath);
                    Log::info('FTP Directory created', ['path' => $currentPath]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not create FTP directory: ' . $e->getMessage());
            // Continue anyway, the directory might already exist
        }
    }

    /**
     * Get list of exported files from FTP
     */
    public function listExportedFiles(): array
    {
        try {
            $disk = Storage::disk('ftp');
            
            if (!$disk->exists($this->ftpTargetDir)) {
                return [
                    'success' => true,
                    'files' => [],
                    'message' => 'Direktori belum ada',
                ];
            }

            $files = $disk->files($this->ftpTargetDir);
            
            $fileList = [];
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if ($extension === 'csv') {
                    try {
                        $size = $disk->size($file);
                        $lastModified = $disk->lastModified($file);
                    } catch (\Exception $e) {
                        $size = 0;
                        $lastModified = null;
                    }

                    $fileList[] = [
                        'name' => basename($file),
                        'path' => $file,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
                    ];
                }
            }

            // Sort by last modified descending
            usort($fileList, function ($a, $b) {
                return ($b['last_modified'] ?? '') <=> ($a['last_modified'] ?? '');
            });

            return [
                'success' => true,
                'files' => $fileList,
                'count' => count($fileList),
            ];

        } catch (\Exception $e) {
            Log::error('Lembur List Files Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengambil daftar file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
