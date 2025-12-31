<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class RABStreamImport
{
    private $lamaBulan;

    public function __construct($lamaBulan)
    {
        $this->lamaBulan = $lamaBulan;
    }

    /**
     * Baca file Excel secara langsung dengan PhpSpreadsheet
     * Fokus pada sheet "Sheet1"
     * Optimized untuk speed dengan rangeToArray
     * Support kolom F untuk Summary Detail RAB
     */
    public function import($filePath)
    {
        try {
            // Load Excel file - Disable calculation cache untuk speed
            $spreadsheet = IOFactory::load($filePath);
            $spreadsheet->getCalculationEngine()->disableCalculationCache();

            // Cari sheet dengan nama "Sheet1"
            $sheet = null;
            $sheetNames = $spreadsheet->getSheetNames();

            foreach ($sheetNames as $sheetName) {
                if (strcasecmp(trim($sheetName), 'Sheet1') === 0) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    break;
                }
            }

            // Jika tidak ditemukan dengan nama persis, cari yang mirip
            if ($sheet === null) {
                foreach ($sheetNames as $sheetName) {
                    if (stripos($sheetName, 'Sheet') !== false && stripos($sheetName, '1') !== false) {
                        $sheet = $spreadsheet->getSheetByName($sheetName);
                        break;
                    }
                }
            }

            if ($sheet === null) {
                throw new \Exception("Sheet 'Sheet1' tidak ditemukan. Sheet yang tersedia: " . implode(', ', $sheetNames));
            }

            // Baca data dari baris 12 ke bawah
            $result = [];
            $maxRowsToRead = 100; // Increased untuk accommodate summary data

            $startRow = 12;
            $endRow = min($startRow + $maxRowsToRead - 1, $sheet->getHighestRow());

            // Load data range sekaligus untuk efisiensi
            // Perlu baca dari kolom B sampai kolom terakhir yang diperlukan
            // Untuk Detail RAB: kolom G sampai G+lamaBulan
            // Untuk Summary: kolom F
            $startCol = 'B'; // Kolom B untuk keterangan
            $endCol = $this->getColumnLetter(7 + $this->lamaBulan); // G=7, jadi 7+lamaBulan

            // Baca range data sekaligus (lebih cepat dari cell by cell)
            $dataRange = $sheet->rangeToArray(
                "{$startCol}{$startRow}:{$endCol}{$endRow}",
                null,  // nullValue
                true,  // calculateFormulas = TRUE (penting!)
                false, // formatData
                false  // returnCellRef
            );

            $rowCounter = 0;
            foreach ($dataRange as $rowIndex => $rowData) {
                $actualRowNumber = $startRow + $rowIndex;

                // Guard: pastikan rowData adalah array
                if (!is_array($rowData)) {
                    continue;
                }

                // Kolom B ada di index 0 (karena range dimulai dari B)
                $keterangan = $rowData[0] ?? '';

                // Trim keterangan
                $keteranganTrimmed = trim((string)$keterangan);

                // Ambil nilai dari kolom F (index 4: B=0, C=1, D=2, E=3, F=4)
                $nilaiKolomF = $rowData[4] ?? null;

                // Jika sel benar-benar kosong (null atau empty string), simpan sebagai null
                // sehingga pemroses summary dapat membedakan antara 0/"-" dan sel kosong
                // Treat null or whitespace-only as truly empty
                if ($nilaiKolomF === null || trim((string)$nilaiKolomF) === '') {
                    $parsedNilaiF = null;
                } else {
                    $parsedNilaiF = $this->parseNumericValue($nilaiKolomF);
                }

                // Ambil nilai dari kolom G sampai G+lamaBulan
                // Kolom G ada di index 5 (B=0, C=1, D=2, E=3, F=4, G=5)
                $values = [];
                $rawValues = []; // Untuk debugging

                for ($j = 0; $j <= $this->lamaBulan; $j++) {
                    $colIndex = 5 + $j; // G=5, H=6, I=7, dst
                    $cellValue = $rowData[$colIndex] ?? null;

                    $columnLetter = $this->getColumnLetter(7 + $j); // G=7
                    $rawValues[] = "[$columnLetter: $cellValue]";

                    // Parse nilai dengan pembulatan
                    $parsedValue = $this->parseNumericValue($cellValue);
                    $values[] = $parsedValue;
                }

                $result[] = [
                    'row_index' => $rowCounter,
                    'excel_row' => $actualRowNumber,
                    'keterangan' => $keteranganTrimmed,
                    'nilai_kolom_f' => $parsedNilaiF, // Nilai kolom F untuk Summary
                    'values' => $values // Array nilai untuk Detail RAB (kolom G+)
                ];

                $rowCounter++;
            }

            // Cleanup
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $dataRange);
            gc_collect_cycles();

            return $result;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Parse nilai numerik dari Excel dengan pembulatan
     */
    private function parseNumericValue($value)
    {
        // Null/kosong/'-' dianggap 0
        if ($value === null || $value === '' || $value === '-' || trim($value) === '-') {
            return 0;
        }

        // Jika sudah dalam format numerik
        if (is_int($value) || is_float($value)) {
            // Bulatkan nilai desimal
            return round((float) $value);
        }

        // Konversi ke string untuk cleaning
        $stringValue = trim((string) $value);

        // Hapus pemisah ribuan (titik) dan ganti koma desimal dengan titik
        $cleaned = str_replace('.', '', $stringValue);
        $cleaned = str_replace(',', '.', $cleaned);

        // Konversi ke float dan bulatkan
        if (is_numeric($cleaned)) {
            return round((float) $cleaned);
        }

        return 0;
    }

    /**
     * Convert column number to Excel column letter (1=A, 27=AA, etc.)
     */
    private function getColumnLetter($columnNumber)
    {
        $letter = '';
        while ($columnNumber > 0) {
            $temp = ($columnNumber - 1) % 26;
            $letter = chr(65 + $temp) . $letter;
            $columnNumber = (int)(($columnNumber - $temp - 1) / 26);
        }
        return $letter;
    }
}
