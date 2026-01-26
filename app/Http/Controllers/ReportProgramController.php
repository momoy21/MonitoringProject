<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Carbon\Carbon;

class ReportProgramController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Parameter dari Request
        $jenis  = $request->jenis_report ?? 'berita_acara'; 
        $status = $request->status ?? 'All';       
        
        $start_raw = $request->start;
        $end_raw   = $request->end;

        // 2. Konversi Tanggal untuk Query Database
        $start = $start_raw ? Carbon::parse($start_raw)->format('Y-m-d') : null;
        $end   = $end_raw ? Carbon::parse($end_raw)->format('Y-m-d') : null;

        $data = collect([]);

        // 3. Logic Query Berita Acara
        if ($jenis == 'berita_acara') {
            $query = DB::table('berita_acara_project')
                ->join('history_proyek', 'berita_acara_project.id_project', '=', 'history_proyek.id_project')
                ->select('berita_acara_project.*', 'history_proyek.namaproject', 'history_proyek.cost_center');

            if ($status && $status != 'All') {
                $query->where('berita_acara_project.status', $status);
            }
            if ($start && $end) {
                $query->whereBetween('berita_acara_project.periode_mulai', [$start, $end]);
            }
            $data = $query->get();
        } 
        // 4. Logic Query Issue Project
        elseif ($jenis == 'issue_project') {
            $query = DB::table('issue_proyek')
                ->join('history_proyek', 'issue_proyek.id_project', '=', 'history_proyek.id_project')
                ->select('issue_proyek.*', 'history_proyek.namaproject');

            if ($status && $status != 'All') {
                $query->where('issue_proyek.status', $status);
            }
            if ($start && $end) {
                $query->whereBetween('issue_proyek.tanggal', [$start, $end]);
            }
            $data = $query->get();
        }

        // Simpan ke session untuk export
        session(['report_data' => $data, 'jenis' => $jenis]);

        return view('report.index', compact('data', 'jenis', 'status', 'start_raw', 'end_raw'));
    }

    public function exportPdf()
    {
        $data = session('report_data');
        $jenis = session('jenis');
        
        if (!$data || $data->isEmpty()) {
            return back()->with('error', 'Data kosong, silahkan klik proses terlebih dahulu');
        }
    
        
        $pdf = Pdf::loadView('report.pdf_template', compact('data', 'jenis'))
                  ->setPaper('a4', 'landscape');
                  
        return $pdf->download('Laporan_Progress_Proyek.pdf');
    }
    
    public function exportExcel()
    {
        $data = session('report_data');
        if (!$data || $data->isEmpty()) {
            return back()->with('error', 'Data kosong, silahkan klik proses terlebih dahulu');
        }
    
        return Excel::download(new ReportExport, 'Laporan_Progress_Proyek.xlsx');
    }
}