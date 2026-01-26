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
        // Mengambil data dari session
        return collect(session('report_data', []));
    }

    // 1. HEADER: Menentukan Judul Kolom di Excel
    public function headings(): array
    {
        $jenis = session('jenis');

        if ($jenis === 'berita_acara') {
            return [
                'Tanggal',
                'Nama Proyek',
                'No Dokumen',
                'Nilai BA'
            ];
        }

        if ($jenis === 'issue_project') {
            return [
                'Tanggal',
                'Issue / Kendala',
                'Mitigasi Issue',
                'Status'
            ];
        }

        return [];
    }

    // 2. MAPPING: Memastikan data yang masuk ke kolom Excel urutannya benar
    public function map($row): array
    {
        $jenis = session('jenis');

        if ($jenis === 'berita_acara') {
            return [
                $row->tanggal,
                $row->nama_proyek,
                $row->no_dokumen,
                $row->nilai_ba,
            ];
        }

        if ($jenis === 'issue_project') {
            return [
                $row->tanggal,
                $row->issue_kendala ?? $row->issue, // Mengantisipasi perbedaan nama field
                $row->mitigasi,
                $row->status,
            ];
        }

        return [];
    }
}