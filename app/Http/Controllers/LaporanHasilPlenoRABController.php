<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RABProyek;
use App\Models\MasterDivisi;
use App\Models\JenisProyek;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanHasilPlenoRABController extends Controller
{
    /**
     * Display the main dashboard page
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', '');
        $endDate = $request->get('end_date', '');
        $divisiList = MasterDivisi::active()->get();
        $jenisProyekList = JenisProyek::all();

        return view('laporanhasilplenorab.index', compact(
            'startDate',
            'endDate',
            'divisiList',
            'jenisProyekList'
        ));
    }

    /**
     * Dashboard 1: Hasil Pleno per Divisi - Summary table + chart data
     */
    public function getDivisiData(Request $request)
    {
        $query = $this->getBaseQuery($request);

        // Get summary grouped by month and divisi
        $summary = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                'divisi',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereNotNull('divisi')
            ->groupBy('bulan', 'divisi')
            ->orderBy('bulan')
            ->get();

        // Get chart data grouped by divisi
        $chartData = (clone $query)->select(
                'divisi',
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TR' THEN 1 ELSE 0 END) as tercapai"),
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TT' THEN 1 ELSE 0 END) as tidak_tercapai")
            )
            ->whereNotNull('divisi')
            ->groupBy('divisi')
            ->get();

        $divisiNames = MasterDivisi::pluck('nama_divisi', 'kode_divisi');
        
        // Format summary with divisi names
        $summaryFormatted = $summary->map(function ($item) use ($divisiNames) {
            return [
                'bulan' => Carbon::parse($item->bulan . '-01')->format('F Y'),
                'bulan_raw' => $item->bulan,
                'divisi' => $divisiNames[$item->divisi] ?? $item->divisi,
                'kode_divisi' => $item->divisi,
                'jumlah' => $item->jumlah,
            ];
        });

        $labels = $chartData->map(fn($item) => $divisiNames[$item->divisi] ?? $item->divisi);

        return response()->json([
            'summary' => $summaryFormatted,
            'labels' => $labels,
            'tercapai' => $chartData->pluck('tercapai'),
            'tidak_tercapai' => $chartData->pluck('tidak_tercapai'),
        ]);
    }

    /**
     * Dashboard 2: Hasil Pleno per Kategori - Summary table + chart data
     */
    public function getKategoriData(Request $request)
    {
        $query = $this->getBaseQuery($request);

        // Get summary grouped by month and kategori
        $summary = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                'hasil_pleno',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereNotNull('hasil_pleno')
            ->groupBy('bulan', 'hasil_pleno')
            ->orderBy('bulan')
            ->get();

        // Get pie chart data
        $pieData = (clone $query)->select(
                'hasil_pleno',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('hasil_pleno')
            ->groupBy('hasil_pleno')
            ->get();

        $tercapai = $pieData->where('hasil_pleno', 'TR')->first()->total ?? 0;
        $tidakTercapai = $pieData->where('hasil_pleno', 'TT')->first()->total ?? 0;

        $summaryFormatted = $summary->map(function ($item) {
            return [
                'bulan' => Carbon::parse($item->bulan . '-01')->format('F Y'),
                'bulan_raw' => $item->bulan,
                'keterangan' => $item->hasil_pleno == 'TR' ? 'Tercapai RKAP' : 'Tidak Tercapai RKAP',
                'hasil_pleno' => $item->hasil_pleno,
                'jumlah' => $item->jumlah,
            ];
        });

        return response()->json([
            'summary' => $summaryFormatted,
            'labels' => ['Tercapai Margin RKAP', 'Tidak Tercapai Margin RKAP'],
            'data' => [$tercapai, $tidakTercapai],
            'colors' => ['#28a745', '#dc3545'],
        ]);
    }

    /**
     * Dashboard 3: Hasil Pleno per Kategori dan Divisi
     */
    public function getDivisiKategoriData(Request $request)
    {
        $query = $this->getBaseQuery($request);

        // Get summary grouped by month, divisi, and hasil_pleno
        $summary = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                'divisi',
                'hasil_pleno',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereNotNull('divisi')
            ->whereNotNull('hasil_pleno')
            ->groupBy('bulan', 'divisi', 'hasil_pleno')
            ->orderBy('bulan')
            ->get();

        // Get chart data by month
        $chartData = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                'divisi',
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TR' THEN 1 ELSE 0 END) as tercapai"),
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TT' THEN 1 ELSE 0 END) as tidak_tercapai")
            )
            ->whereNotNull('divisi')
            ->groupBy('bulan', 'divisi')
            ->orderBy('bulan')
            ->get();

        $divisiNames = MasterDivisi::pluck('nama_divisi', 'kode_divisi');
        $divisiList = MasterDivisi::active()->pluck('kode_divisi')->toArray();
        $months = $chartData->pluck('bulan')->unique()->sort()->values();

        $datasets = [];
        $colors = [
            'DT' => ['tercapai' => '#4CAF50', 'tidak' => '#81C784'],
            'ERP' => ['tercapai' => '#2196F3', 'tidak' => '#64B5F6'],
            'Infra' => ['tercapai' => '#9C27B0', 'tidak' => '#BA68C8'],
        ];

        foreach ($divisiList as $divisi) {
            $divisiChartData = $chartData->where('divisi', $divisi);
            $tercapaiData = [];
            $tidakData = [];

            foreach ($months as $month) {
                $item = $divisiChartData->where('bulan', $month)->first();
                $tercapaiData[] = $item->tercapai ?? 0;
                $tidakData[] = $item->tidak_tercapai ?? 0;
            }

            $divisiName = $divisiNames[$divisi] ?? $divisi;
            $color = $colors[$divisi] ?? ['tercapai' => '#607D8B', 'tidak' => '#90A4AE'];
            // Convert hex to rgba for a faded color for "Tidak Tercapai"
            $hex = ltrim($color['tidak'], '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $rgbaTidak = "rgba($r,$g,$b,0.45)";

            // Use the division code as stack id so the two datasets for the division stack together
            $datasets[] = [
                'label' => $divisiName . ' - Tercapai RKAP',
                'data' => $tercapaiData,
                'backgroundColor' => $color['tercapai'],
                'stack' => $divisi . '-T',
            ];
            $datasets[] = [
                'label' => $divisiName . ' - Tidak Tercapai RKAP',
                'data' => $tidakData,
                'backgroundColor' => $rgbaTidak,
                'stack' => $divisi . '-NT',
            ];
        }

        $summaryFormatted = $summary->map(function ($item) use ($divisiNames) {
            return [
                'bulan' => Carbon::parse($item->bulan . '-01')->format('F Y'),
                'bulan_raw' => $item->bulan,
                'divisi' => $divisiNames[$item->divisi] ?? $item->divisi,
                'kode_divisi' => $item->divisi,
                'hasil' => $item->hasil_pleno == 'TR' ? 'Tercapai RKAP' : 'Tidak Tercapai RKAP',
                'hasil_pleno' => $item->hasil_pleno,
                'jumlah' => $item->jumlah,
            ];
        });

        $labels = $months->map(fn($m) => Carbon::parse($m . '-01')->format('M Y'));

        return response()->json([
            'summary' => $summaryFormatted,
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }

    /**
     * Dashboard 4: Hasil Pleno per Jenis Proyek dan Kategori
     */
    public function getJenisProyekData(Request $request)
    {
        $query = $this->getBaseQuery($request);

        // Get summary grouped by month, kategori, and jenis_proyek with percentage
        $summary = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                'hasil_pleno',
                'jenis_proyek',
                DB::raw('COUNT(*) as jumlah')
            )
            ->whereNotNull('jenis_proyek')
            ->whereNotNull('hasil_pleno')
            ->groupBy('bulan', 'hasil_pleno', 'jenis_proyek')
            ->orderBy('bulan')
            ->get();

        // Calculate totals for percentage
        $totals = (clone $query)->select(
                DB::raw("DATE_FORMAT(tgl_input, '%Y-%m') as bulan"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        // Get chart data
        $chartData = (clone $query)->select(
                'jenis_proyek',
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TR' THEN 1 ELSE 0 END) as tercapai"),
                DB::raw("SUM(CASE WHEN hasil_pleno = 'TT' THEN 1 ELSE 0 END) as tidak_tercapai")
            )
            ->whereNotNull('jenis_proyek')
            ->groupBy('jenis_proyek')
            ->get();

        $jenisNames = JenisProyek::pluck('nama_jenis', 'kode_jenis');

        $summaryFormatted = $summary->map(function ($item) use ($jenisNames, $totals) {
            $total = $totals[$item->bulan] ?? 1;
            $percentage = round(($item->jumlah / $total) * 100, 0);
            return [
                'bulan' => Carbon::parse($item->bulan . '-01')->format('F Y'),
                'bulan_raw' => $item->bulan,
                'keterangan' => $item->hasil_pleno == 'TR' ? 'Tercapai RKAP' : 'Tidak Tercapai RKAP',
                'hasil_pleno' => $item->hasil_pleno,
                'jenis_proyek' => $jenisNames[$item->jenis_proyek] ?? $item->jenis_proyek,
                'kode_jenis' => $item->jenis_proyek,
                'prosentase' => $percentage . '%',
                'jumlah' => $item->jumlah,
            ];
        });

        $labels = $chartData->map(fn($item) => $jenisNames[$item->jenis_proyek] ?? $item->jenis_proyek);

        return response()->json([
            'summary' => $summaryFormatted,
            'labels' => $labels,
            'tercapai' => $chartData->pluck('tercapai'),
            'tidak_tercapai' => $chartData->pluck('tidak_tercapai'),
        ]);
    }

    /**
     * Get detail data for clicked chart element
     */
    public function getDetailData(Request $request)
    {
        $query = $this->getBaseQuery($request);

        // Apply additional filters based on clicked element
        if ($request->has('divisi') && $request->divisi) {
            $query->where('divisi', $request->divisi);
        }
        if ($request->has('hasil_pleno') && $request->hasil_pleno) {
            $query->where('hasil_pleno', $request->hasil_pleno);
        }
        if ($request->has('jenis_proyek') && $request->jenis_proyek) {
            $query->where('jenis_proyek', $request->jenis_proyek);
        }
        if ($request->has('bulan') && $request->bulan) {
            $query->whereRaw("DATE_FORMAT(tgl_input, '%Y-%m') = ?", [$request->bulan]);
        }

        $data = $query->with(['konsumen', 'masterDivisi'])
            ->orderBy('tgl_input', 'desc')
            ->get();

        $divisiNames = MasterDivisi::pluck('nama_divisi', 'kode_divisi');

        $result = $data->map(function ($item) use ($divisiNames) {
            return [
                'tgl_pengajuan' => $item->tgl_input ? Carbon::parse($item->tgl_input)->format('d/m/Y') : '-',
                'io' => $item->dokumen_io ?? '-',
                'cost_center' => $item->cost_center ?? '-',
                'description' => $item->nama_project ?? '-',
                'customer' => $item->konsumen->konsumen ?? '-',
                'divisi' => $divisiNames[$item->divisi] ?? $item->divisi ?? '-',
                'pm' => $item->pm ?? '-',
                'keterangan' => $item->keterangan_text ?? '-',
                'hasil_pleno' => $item->hasil_pleno_text ?? '-',
            ];
        });

        return response()->json(['data' => $result]);
    }

    /**
     * Build base query with date range filter
     */
    private function getBaseQuery(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = RABProyek::query()->whereNotNull('hasil_pleno');

        if ($startDate && $endDate) {
            $query->whereBetween('tgl_input', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } elseif ($startDate) {
            $query->where('tgl_input', '>=', Carbon::parse($startDate)->startOfDay());
        } elseif ($endDate) {
            $query->where('tgl_input', '<=', Carbon::parse($endDate)->endOfDay());
        }

        return $query;
    }
}
