<?php

namespace App\Imports;

use App\Models\Penugasan;
use App\Models\HistoryProyek;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PenugasanImport implements ToModel, WithHeadingRow
{
    private string $idPenugasan;
    private string $noSurat;
    private int $norut = 0;

    public function __construct($idPenugasan, $noSurat)
    {
        $this->idPenugasan = $idPenugasan;
        $this->noSurat = $noSurat;
    }

    public function model(array $row)
    {
        /**
         * HEADER EXCEL:
         * Cost Center    -> cost_center
         * Periode Awal   -> periode_awal
         * Periode Akhir  -> periode_akhir
         */

        $costcenter = $row['cost_center'] ?? null;
        $nik        = $row['nik'] ?? null;
        $jabatan    = $row['jabatan'] ?? null;
        $awal       = $row['periode_awal'] ?? null;
        $akhir      = $row['periode_akhir'] ?? null;
        $bobot      = $row['bobot'] ?? 0;
        $ket        = $row['keterangan'] ?? null;

        if (!$costcenter || !$nik) {
            return null;
        }

        $this->norut++;

        $proyek = HistoryProyek::where('cost_center', $costcenter)->first();

        // ID UNIK PER ROW
        $idPenugasanRow = $this->idPenugasan . str_pad($this->norut, 2, '0', STR_PAD_LEFT) . substr(uniqid(), -4);


        return new Penugasan([
            'IDPenugasan'  => $idPenugasanRow,
            'cost_center'  => $costcenter,
            'Norut'        => $this->norut,
            'NIK'          => $nik,
            'NoSurat'      => $this->noSurat,
            'Dokumen_IO'   => $proyek->Dokumen_IO ?? null,
            'Jabatan'      => $jabatan,
            'Periodeawal'  => $this->parseDate($awal),
            'Periodeakhir' => $this->parseDate($akhir),
            'Bobot'        => $bobot,
            'Status'       => 'A',
            'Keterangan'   => $ket,
        ]);
    }

    private function parseDate($value)
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        return date('Y-m-d', strtotime($value));
    }
}