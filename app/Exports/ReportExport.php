<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return collect(session('report_data', []));
    }

    public function headings(): array
    {
        $jenis = session('jenis');
        if ($jenis === 'berita_acara') {
            return ['No', 'Tanggal', 'Nama Proyek', 'No Dokumen', 'Nilai BA'];
        }
        return ['No', 'Tanggal', 'Nama Proyek', 'Issue / Kendala', 'Mitigasi', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        $jenis = session('jenis');

        if ($jenis === 'berita_acara') {
            return [
                $no,
                $row->periode_mulai, 
                $row->namaproject,
                $row->no_ba,         
                $row->nilai_ba,      
            ];
        }

        return [
            $no,
            $row->tanggal,
            $row->namaproject,
            $row->issue,
            $row->mitigasi,
            $row->status == 'O' ? 'Open' : 'Close',
        ];
    }
}